<?php

namespace Tests\Feature;

use App\Models\ReligiousAccount;
use App\Notifications\ReligiousPasswordReset;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ReligiousAccountIdentityTest extends TestCase
{
    private string $key = '';

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'identity_test', 'database.connections.identity_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ], 'religious_account.google_client_id' => 'haq-web.apps.googleusercontent.com', 'religious_account.recovery_enabled' => true]);
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        foreach (['2026_10_13_120000_create_religious_accounts_and_quran_sync', '2026_01_12_150326_create_personal_access_tokens_table', '2026_10_15_000001_create_religious_content_sync', '2026_10_24_120000_create_religious_module_storage', '2026_10_26_120000_extend_religious_account_identity'] as $migration) {
            (require database_path('migrations/'.$migration.'.php'))->up();
        }
        $key = \phpseclib3\Crypt\RSA::createKey(2048);
        $this->key = $key->toString('PKCS8');
        $details = json_decode($key->getPublicKey()->toString('JWK'), true)['keys'][0];
        Http::preventStrayRequests();
        Http::fake(['www.googleapis.com/oauth2/v3/certs' => Http::response(['keys' => [[
            'kid' => 'test-key', 'kty' => 'RSA', 'alg' => 'RS256',
            'n' => $details['n'], 'e' => $details['e'],
        ]]], 200, ['Cache-Control' => 'public, max-age=3600'])]);
    }

    protected function tearDown(): void
    {
        DB::purge('identity_test');
        parent::tearDown();
    }

    private function owner(): ReligiousAccount
    {
        return ReligiousAccount::create(['name' => 'قارئ', 'email' => 'reader@example.test', 'password' => 'test-password']);
    }

    private function jwt(array $changes = []): string
    {
        return JWT::encode(array_replace(['iss' => 'https://accounts.google.com', 'aud' => 'haq-web.apps.googleusercontent.com',
            'sub' => 'google-subject-1', 'email' => 'google@example.test', 'email_verified' => true,
            'iat' => time(), 'exp' => time() + 3600, 'name' => 'قارئ Google'], $changes), $this->key, 'RS256', 'test-key');
    }

    public function test_google_verifies_signature_audience_issuer_expiry_and_email_before_creating_any_account(): void
    {
        foreach ([['aud' => 'attacker'], ['iss' => 'https://attacker.test'], ['exp' => time() - 300], ['email_verified' => false]] as $change) {
            $this->postJson('/api/v1/religious/account/google', ['id_token' => $this->jwt($change), 'username' => 'hazemayad'])->assertUnprocessable();
        }
        $token = $this->jwt();
        $parts = explode('.', $token);
        $parts[2] = str_repeat('a', strlen($parts[2]));
        $this->postJson('/api/v1/religious/account/google', ['id_token' => implode('.', $parts), 'username' => 'hazemayad'])->assertUnprocessable();
        $this->assertSame(0, ReligiousAccount::count());
        $this->assertSame(0, DB::table('personal_access_tokens')->count());
    }

    public function test_google_creates_only_a_religious_account_and_username_is_normalized_unique(): void
    {
        $this->postJson('/api/v1/religious/account/google', ['id_token' => $this->jwt()])->assertUnprocessable();
        $result = $this->postJson('/api/v1/religious/account/google', ['id_token' => $this->jwt(), 'username' => 'HazemAyad'])
            ->assertOk()->assertJsonPath('data.account.username', 'hazemayad')->assertJsonPath('data.account.password_enabled', false);
        $this->assertFalse(Schema::hasTable('users'));
        $this->assertArrayNotHasKey('google_subject', $result->json('data.account'));
        $this->assertSame(['quran:sync'], DB::table('personal_access_tokens')->first()->abilities ? json_decode(DB::table('personal_access_tokens')->first()->abilities, true) : []);
        $this->postJson('/api/v1/religious/account/google', ['id_token' => $this->jwt()])->assertOk();
        $this->assertSame(1, ReligiousAccount::count());
        $this->postJson('/api/v1/religious/account/google', ['id_token' => $this->jwt(['sub' => 'other', 'email' => 'other@example.test']), 'username' => 'HAZEMAYAD'])->assertUnprocessable();
    }

    public function test_matching_email_cannot_auto_link_and_authenticated_link_checks_email(): void
    {
        $owner = $this->owner();
        $token = $owner->createToken('test', ['quran:sync'])->plainTextToken;
        $this->postJson('/api/v1/religious/account/google', ['id_token' => $this->jwt(['email' => $owner->email]), 'username' => 'newreader'])->assertUnprocessable();
        $this->withToken($token)->postJson('/api/v1/religious/account/google/link', ['id_token' => $this->jwt()])->assertUnprocessable();
        $this->withToken($token)->postJson('/api/v1/religious/account/google/link', ['id_token' => $this->jwt(['email' => $owner->email])])->assertOk()->assertJsonPath('data.google_linked', true);
        $this->assertSame(1, ReligiousAccount::count());
    }

    public function test_profile_username_completion_is_protected_and_cannot_modify_roles_or_email(): void
    {
        $owner = $this->owner();
        $token = $owner->createToken('test', ['quran:sync'])->plainTextToken;
        $this->patchJson('/api/v1/religious/account/profile', ['username' => 'hazemayad'])->assertUnauthorized();
        $this->withToken($token)->patchJson('/api/v1/religious/account/profile', ['username' => 'abc'])->assertUnprocessable();
        $this->withToken($token)->patchJson('/api/v1/religious/account/profile', ['username' => 'HazemAyad', 'name' => 'حازم', 'email' => 'attacker@example.test', 'roles' => ['admin']])->assertOk()
            ->assertJsonPath('data.username', 'hazemayad')->assertJsonPath('data.email', $owner->email);
        $this->assertSame('حازم', $owner->fresh()->name);
    }

    public function test_recovery_uses_separate_hashed_expiring_single_use_token_and_revokes_all_sessions(): void
    {
        Notification::fake();
        $owner = $this->owner();
        $owner->createToken('test', ['quran:sync']);
        $this->postJson('/api/v1/religious/account/forgot-password', ['email' => strtoupper($owner->email)])->assertOk();
        $token = '';
        Notification::assertSentTo($owner, ReligiousPasswordReset::class, function ($notification) use (&$token) {
            $token = $notification->token;

            return true;
        });
        $this->assertNotSame($token, DB::table('religious_password_reset_tokens')->first()->token);
        $this->assertFalse(Schema::hasTable('password_reset_tokens'));
        $this->postJson('/api/v1/religious/account/reset-password', ['email' => $owner->email, 'token' => 'invalid', 'password' => 'new-test-password', 'password_confirmation' => 'new-test-password'])->assertUnprocessable();
        $data = ['email' => $owner->email, 'token' => $token, 'password' => 'new-test-password', 'password_confirmation' => 'new-test-password'];
        $this->postJson('/api/v1/religious/account/reset-password', $data)->assertOk();
        $this->assertTrue(Hash::check('new-test-password', $owner->fresh()->password));
        $this->assertSame(0, DB::table('personal_access_tokens')->count());
        $this->postJson('/api/v1/religious/account/reset-password', $data)->assertUnprocessable();
    }

    public function test_expired_recovery_and_unconfigured_services_do_not_claim_success(): void
    {
        Notification::fake();
        $owner = $this->owner();
        $token = \Illuminate\Support\Facades\Password::broker('religious')->createToken($owner);
        DB::table('religious_password_reset_tokens')->update(['created_at' => now()->subMinutes(31)]);
        $this->postJson('/api/v1/religious/account/reset-password', ['email' => $owner->email, 'token' => $token, 'password' => 'new-test-password', 'password_confirmation' => 'new-test-password'])->assertUnprocessable();
        config(['religious_account.google_client_id' => '', 'religious_account.recovery_enabled' => false]);
        $this->getJson('/api/v1/religious/account/capabilities')->assertJsonPath('data.google', false)->assertJsonPath('data.recovery', false);
        $this->postJson('/api/v1/religious/account/google', ['id_token' => $this->jwt()])->assertStatus(503);
        $this->postJson('/api/v1/religious/account/forgot-password', ['email' => $owner->email])->assertStatus(503);
        Notification::assertNothingSent();
    }

    public function test_delete_requires_identity_and_cascades_only_this_accounts_data_and_tokens(): void
    {
        $owner = $this->owner();
        $other = ReligiousAccount::create(['name' => 'آخر', 'email' => 'other@example.test', 'password' => 'test-password']);
        foreach ([$owner, $other] as $account) {
            DB::table('quran_sync_entities')->insert(['religious_account_id' => $account->id, 'entity_key' => 'surah:1', 'value' => 'true', 'deleted' => false, 'revision' => 1]);
            DB::table('religious_personal_entities')->insert(['religious_account_id' => $account->id, 'entity_key' => 'habit:'.Str::uuid(), 'value' => '{}', 'deleted' => false, 'revision' => 1]);
        }
        $token = $owner->createToken('test', ['quran:sync'])->plainTextToken;
        $other->createToken('other', ['quran:sync']);
        $this->withToken($token)->deleteJson('/api/v1/religious/account', ['confirmation' => 'DELETE', 'password' => 'wrong'])->assertUnprocessable();
        $this->withToken($token)->deleteJson('/api/v1/religious/account', ['confirmation' => 'DELETE', 'password' => 'test-password'])->assertOk();
        $this->assertNull($owner->fresh());
        $this->assertNotNull($other->fresh());
        $this->assertSame(1, DB::table('personal_access_tokens')->count());
        $this->assertSame(1, DB::table('quran_sync_entities')->count());
        $this->assertSame(1, DB::table('religious_personal_entities')->count());
    }
}
