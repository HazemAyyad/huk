<?php

namespace Tests\Feature;

use App\Models\AppAccount;
use App\Models\ReligiousAccount;
use App\Models\User;
use App\Services\Religious\AppAdminAccess;
use App\Services\Religious\AppLocalization;
use App\Services\Religious\AppSessionProof;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class HaqAppAccessTest extends TestCase
{
    public function withToken($token, $type = 'Bearer')
    {
        $this->app['auth']->forgetGuards();

        return parent::withToken($token, $type);
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'haq_app_test', 'database.connections.haq_app_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ], 'haq_app.enabled' => true, 'cache.default' => 'array', 'laratrust.cache.enabled' => false]);
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        require_once database_path('migrations/2025_07_04_180710_laratrust_setup_tables.php');
        (new \LaratrustSetupTables)->up();
        foreach (['0001_01_01_000000_create_users_table',
            '2026_10_13_120000_create_religious_accounts_and_quran_sync', '2026_01_12_150326_create_personal_access_tokens_table',
            '2026_10_26_120000_extend_religious_account_identity', '2026_10_30_120000_create_haq_app_localization'] as $migration) {
            (require database_path('migrations/'.$migration.'.php'))->up();
        }
        $pair = sodium_crypto_sign_keypair();
        config(['haq_app.signing_key' => base64_encode(sodium_crypto_sign_secretkey($pair))]);
        app(AppLocalization::class)->initialize();
    }

    protected function tearDown(): void
    {
        DB::purge('haq_app_test');
        parent::tearDown();
    }

    private function religious(string $email = 'reader@example.test'): ReligiousAccount
    {
        return ReligiousAccount::create(['name' => 'Reader', 'email' => $email, 'password' => 'testing-password']);
    }

    private function staff(string $role = 'superadmin'): User
    {
        $user = User::create(['name' => 'Staff', 'email' => 'staff@example.test', 'password' => 'testing-password']);
        $id = DB::table('roles')->insertGetId(['name' => $role, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('role_user')->insert(['user_id' => $user->id, 'role_id' => $id, 'user_type' => $user->getMorphClass()]);

        return $user;
    }

    private function admin(array $permissions = [], array $locales = []): array
    {
        $staff = $this->staff();
        $account = AppAccount::create(['user_id' => $staff->id, 'association_enabled' => true]);
        $id = DB::table('app_admin_memberships')->insertGetId(['app_account_id' => $account->id, 'permissions' => json_encode($permissions ?: array_keys(AppAdminAccess::PERMISSIONS)),
            'locales' => json_encode($locales), 'active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $personal = $account->createToken('app-session', app(AppSessionProof::class)->abilities($account, 'association'), now()->addDay())->plainTextToken;
        $token = $this->withToken($personal)->postJson('/api/v2/admin/session')->assertOk()->json('data.token');

        return [$token, $account, $id, $personal];
    }

    public function test_three_account_area_types_do_not_imply_an_administrative_role(): void
    {
        $religious = $this->religious();
        $staff = $this->staff('user');
        $this->postJson('/api/v2/account/login', ['provider' => 'religious', 'email' => $religious->email, 'password' => 'testing-password'])
            ->assertOk()->assertJsonPath('data.account.areas', ['religious'])->assertJsonPath('data.account.admin_access', false);
        $this->postJson('/api/v2/account/login', ['provider' => 'association', 'email' => $staff->email, 'password' => 'testing-password'])
            ->assertOk()->assertJsonPath('data.account.areas', ['association'])->assertJsonMissingPath('data.religious_session');
        // Explicitly link, never by equal email. Existing duplicate aggregates require a separate reviewed merge.
        $association = AppAccount::where('user_id', $staff->id)->first();
        $religiousOnly = AppAccount::where('religious_account_id', $religious->id)->first();
        $religiousOnly->delete();
        $association->update(['religious_account_id' => $religious->id, 'religious_enabled' => true]);
        $this->postJson('/api/v2/account/login', ['provider' => 'association', 'email' => $staff->email, 'password' => 'testing-password'])
            ->assertOk()->assertJsonPath('data.account.areas', ['association', 'religious'])
            ->assertJsonPath('data.account.admin_access', false)->assertJsonPath('data.religious_session.account.id', $religious->id);
    }

    public function test_matching_emails_and_legacy_personal_tokens_never_grant_admin(): void
    {
        $staff = $this->staff();
        $religious = $this->religious($staff->email);
        $token = $religious->createToken('religious-app', ['quran:sync'])->plainTextToken;
        $this->withToken($token)->postJson('/api/v2/account/exchange')->assertOk()->assertJsonPath('data.account.admin_access', false);
        $this->withToken($token)->getJson('/api/v2/admin/languages')->assertForbidden();
        $this->assertNull(AppAccount::where('religious_account_id', $religious->id)->first()->user_id);
    }

    public function test_staff_permissions_are_intersection_of_laratrust_and_explicit_grant_and_locale(): void
    {
        $staff = $this->staff('employee');
        $account = AppAccount::create(['user_id' => $staff->id, 'association_enabled' => true]);
        DB::table('app_admin_memberships')->insert(['app_account_id' => $account->id, 'permissions' => json_encode(array_keys(AppAdminAccess::PERMISSIONS)), 'locales' => '["en"]', 'active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $personal = $account->createToken('app-session', app(AppSessionProof::class)->abilities($account, 'association'), now()->addDay())->plainTextToken;
        $this->withToken($personal)->postJson('/api/v2/admin/session')->assertForbidden();
        foreach (['read_app_admin', 'update_app_translations'] as $permission) {
            $id = DB::table('permissions')->insertGetId(['name' => $permission]);
            DB::table('permission_user')->insert(['permission_id' => $id, 'user_id' => $staff->id, 'user_type' => $staff->getMorphClass()]);
        }
        $response = $this->withToken($personal)->postJson('/api/v2/admin/session')->assertOk()->assertJsonPath('data.permissions', ['admin.enter', 'translations.edit']);
        $admin = $response->json('data.token');
        $this->withToken($admin)->getJson('/api/v2/admin/languages/ar/records')->assertForbidden();
        $this->withToken($admin)->putJson('/api/v2/admin/languages/en/records', ['key' => 'ui/nav.home', 'value' => 'Start', 'revision' => 1])->assertOk();
        $this->withToken($admin)->postJson('/api/v2/admin/languages/en/review', ['key' => 'ui/nav.home', 'revision' => 2])->assertForbidden();
        $this->withToken($admin)->postJson('/api/v2/admin/languages/en/publish', ['revision' => 1])->assertForbidden();
        DB::table('permission_user')->delete();
        $this->withToken($admin)->getJson('/api/v2/admin/languages')->assertForbidden();
    }

    public function test_revocation_expiry_and_logout_invalidate_administration(): void
    {
        [$token, $account, $grant, $personal] = $this->admin();
        $this->withToken($token)->getJson('/api/v2/admin/languages')->assertOk()->assertJsonCount(35, 'data');
        DB::table('app_admin_memberships')->where('id', $grant)->update(['active' => false]);
        $this->withToken($token)->getJson('/api/v2/admin/languages')->assertForbidden();
        DB::table('app_admin_memberships')->where('id', $grant)->update(['active' => true]);
        $this->withToken($personal)->postJson('/api/v2/account/logout')->assertOk();
        $this->withToken($token)->getJson('/api/v2/admin/languages')->assertUnauthorized();
        $token = $account->createToken('app-admin:0', ['app:admin'], now()->subMinute())->plainTextToken;
        $this->withToken($token)->getJson('/api/v2/admin/languages')->assertUnauthorized();
    }

    public function test_edit_conflicts_placeholders_review_and_publication_are_enforced(): void
    {
        [$token] = $this->admin();
        $this->withToken($token)->postJson('/api/v2/admin/languages/en/publish', ['revision' => 1])->assertUnprocessable();
        $this->withToken($token)->putJson('/api/v2/admin/languages/en/records', ['key' => 'ui/nav.home', 'value' => 'Changed', 'revision' => 1])->assertOk();
        $this->withToken($token)->putJson('/api/v2/admin/languages/en/records', ['key' => 'ui/nav.home', 'value' => 'Lost edit', 'revision' => 1])->assertConflict();
        $this->withToken($token)->putJson('/api/v2/admin/languages/en/records', ['key' => 'ui/admin.coverage', 'value' => 'Missing variables', 'revision' => 1])->assertUnprocessable();
        $this->withToken($token)->putJson('/api/v2/admin/languages/en/records', ['key' => 'ui/nav.home', 'value' => '<script>bad</script>', 'revision' => 2])->assertUnprocessable();
        $this->withToken($token)->postJson('/api/v2/admin/languages/en/review', ['key' => 'ui/nav.home', 'revision' => 1])->assertConflict();
        // Test-only review of all seed values; production requires explicit review.
        DB::table('app_translation_drafts')->where('locale', 'en')->update(['status' => 'reviewed', 'reviewer_id' => 1]);
        $response = $this->withToken($token)->postJson('/api/v2/admin/languages/en/publish', ['revision' => 1])->assertOk();
        $id = $response->json('data.release_id');
        $release = $this->getJson('/api/v2/app-content/releases/'.$id)->assertOk()->json('data');
        $this->assertSame(hash('sha256', $release['payload']), $release['sha256']);
        $secret = base64_decode(config('haq_app.signing_key'));
        $this->assertTrue(sodium_crypto_sign_verify_detached(base64_decode($release['signature']), $release['payload'], sodium_crypto_sign_publickey_from_secretkey($secret)));
        $this->assertSame('Changed', json_decode($release['payload'], true)['strings']['nav.home']);
        $this->withToken($token)->postJson('/api/v2/admin/languages/en/publish', ['revision' => 1])->assertConflict();
        $this->getJson('/api/v2/app-content/bootstrap')->assertJsonCount(1, 'data.languages');
        $this->assertArrayNotHasKey('permissions', json_decode($release['payload'], true));
        $this->assertArrayNotHasKey('editor_id', json_decode($release['payload'], true));
    }

    public function test_unreviewed_content_cannot_leak_and_original_scripture_is_untouched(): void
    {
        [$token] = $this->admin();
        $original = app(\App\Services\Religious\ReligiousCatalog::class)->dataset();
        $item = $original['items'][0];
        $value = ['title' => 'Translated title', 'text' => 'Meaning translation', 'source' => 'Reviewed source', 'sourceUrl' => 'https://example.test/source'];
        $this->withToken($token)->putJson('/api/v2/admin/languages/en/records', ['key' => 'content/'.$item['id'], 'value' => $value, 'revision' => 0])->assertOk();
        DB::table('app_translation_drafts')->where('locale', 'en')->where('resource_key', 'like', 'ui/%')->update(['status' => 'reviewed']);
        $id = $this->withToken($token)->postJson('/api/v2/admin/languages/en/publish', ['revision' => 1])->assertOk()->json('data.release_id');
        $payload = json_decode($this->getJson('/api/v2/app-content/releases/'.$id)->json('data.payload'), true);
        $this->assertEmpty($payload['content']);
        $this->withToken($token)->postJson('/api/v2/admin/languages/en/review', ['key' => 'content/'.$item['id'], 'revision' => 1])->assertOk();
        $this->assertSame($original, app(\App\Services\Religious\ReligiousCatalog::class)->dataset());
    }

    public function test_password_change_and_area_revocation_invalidate_aggregate_and_admin_sessions(): void
    {
        [$token, $account, , $personal] = $this->admin();
        $user = User::findOrFail($account->user_id);
        $user->password = 'a-new-password';
        $user->save();
        $this->withToken($personal)->getJson('/api/v2/account/session')->assertUnauthorized();
        $this->withToken($token)->getJson('/api/v2/admin/languages')->assertUnauthorized();
        $account->association_enabled = false;
        $account->save();
        $this->withToken($personal)->getJson('/api/v2/account/session')->assertUnauthorized();
    }

    public function test_arabic_quran_cannot_be_edited_and_restore_creates_new_signed_revision(): void
    {
        [$token] = $this->admin();
        $quran = collect(app(\App\Services\Religious\ReligiousCatalog::class)->dataset()['items'])->firstWhere('type', 'quran');
        $this->withToken($token)->putJson('/api/v2/admin/languages/ar/records', ['key' => 'content/'.$quran['id'],
            'value' => ['title' => 'عنوان', 'text' => 'نص مغاير', 'source' => 'مصدر', 'sourceUrl' => 'https://example.test/source'], 'revision' => 0])->assertUnprocessable();
        DB::table('app_translation_drafts')->where('locale', 'en')->update(['status' => 'reviewed']);
        $first = $this->withToken($token)->postJson('/api/v2/admin/languages/en/publish', ['revision' => 1])->assertOk()->json('data.release_id');
        $this->withToken($token)->postJson('/api/v2/admin/languages/en/publish', ['revision' => 2, 'restore_release_id' => $first])->assertOk()->assertJsonPath('data.revision', 3);
        $this->assertSame(2, DB::table('app_content_releases')->count());
        config(['haq_app.enabled' => false]);
        $this->getJson('/api/v2/app-content/bootstrap')->assertStatus(503);
        $this->withToken($token)->getJson('/api/v2/admin/languages')->assertStatus(503);
    }

    public function test_linking_two_preexisting_aggregates_requires_both_credentials_and_preserves_staff_grant(): void
    {
        [$adminToken, $association, $grant, $personal] = $this->admin();
        $religious = $this->religious();
        $other = AppAccount::create(['religious_account_id' => $religious->id, 'religious_enabled' => true]);
        $old = $other->createToken('app-session', app(AppSessionProof::class)->abilities($other, 'religious'), now()->addDay())->plainTextToken;
        $input = ['provider' => 'religious', 'email' => $religious->email, 'password' => 'testing-password'];
        $this->withToken($personal)->postJson('/api/v2/account/link', [...$input, 'current_password' => 'wrong'])->assertUnprocessable();
        $this->assertSame(1, count($association->fresh()->areas()));
        $response = $this->withToken($personal)->postJson('/api/v2/account/link', [...$input, 'current_password' => 'testing-password'])
            ->assertOk()->assertJsonPath('data.account.areas', ['association', 'religious'])->assertJsonPath('data.account.admin_access', true);
        $this->assertSame($association->id, $response->json('data.account.id'));
        $this->assertFalse($other->fresh()->active);
        $this->assertSame($religious->id, $association->fresh()->religious_account_id);
        $this->assertTrue(DB::table('app_admin_memberships')->where('id', $grant)->exists());
        $this->withToken($old)->getJson('/api/v2/account/session')->assertUnauthorized();
        $this->withToken($adminToken)->getJson('/api/v2/admin/languages')->assertUnauthorized();
    }

    public function test_team_grants_use_laratrust_preserve_other_permissions_and_forbid_self_escalation(): void
    {
        [$token, , $self] = $this->admin();
        $staff = User::create(['name' => 'Editor', 'email' => 'editor@example.test', 'password' => 'testing-password']);
        $account = AppAccount::create(['user_id' => $staff->id, 'association_enabled' => true]);
        $grant = DB::table('app_admin_memberships')->insertGetId(['app_account_id' => $account->id,
            'permissions' => '[]', 'locales' => '[]', 'active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $unrelated = DB::table('permissions')->insertGetId(['name' => 'read_humanitarian_case']);
        DB::table('permission_user')->insert(['permission_id' => $unrelated, 'user_id' => $staff->id, 'user_type' => $staff->getMorphClass()]);
        $input = ['active' => true, 'permissions' => ['admin.enter', 'translations.edit'], 'locales' => ['en']];
        $this->withToken($token)->patchJson('/api/v2/admin/team/'.$self, $input)->assertForbidden();
        $this->withToken($token)->patchJson('/api/v2/admin/team/'.$grant, $input)->assertOk();
        $this->assertTrue(DB::table('permission_user')->where('user_id', $staff->id)->where('permission_id', $unrelated)->exists());
        $this->assertSame(['admin.enter', 'translations.edit'], app(AppAdminAccess::class)->session($account)['permissions']);
        $input['permissions'] = ['admin.enter'];
        $this->withToken($token)->patchJson('/api/v2/admin/team/'.$grant, $input)->assertOk();
        $this->assertSame(['admin.enter'], app(AppAdminAccess::class)->session($account)['permissions']);
    }
}
