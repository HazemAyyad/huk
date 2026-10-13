<?php

namespace Tests\Feature;

use App\Models\ReligiousAccount;
use App\Models\User;
use App\Services\Quran\QuranSync;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Only dedicated in-memory tables: never application migrations or local DB. */
final class QuranSyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'quran_test', 'database.connections.quran_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true], 'cache.default' => 'array']);
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        (require database_path('migrations/2026_10_13_120000_create_religious_accounts_and_quran_sync.php'))->up();
        (require database_path('migrations/2026_01_12_150326_create_personal_access_tokens_table.php'))->up();
    }

    protected function tearDown(): void
    {
        DB::purge('quran_test');
        parent::tearDown();
    }

    private function owner(string $email = 'reader@example.test'): ReligiousAccount
    {
        return ReligiousAccount::create(['name' => 'قارئ', 'email' => $email, 'password' => 'quran-test-pass']);
    }

    private function op(string $key = 'favorite:2:3', int $revision = 0, bool $deleted = false): array
    {
        return ['id' => (string) Str::uuid(), 'key' => $key, 'base_revision' => $revision, 'deleted' => $deleted, 'value' => $deleted ? null : '2026-10-13T00:00:00Z'];
    }

    public function test_registration_token_owns_only_the_religious_account(): void
    {
        $response = $this->postJson('/api/v1/religious/account/register', ['name' => 'قارئ', 'email' => 'Reader@Example.test', 'password' => 'quran-test-pass'])->assertCreated()->assertJsonPath('data.account.email', 'reader@example.test');
        $this->assertFalse(Schema::hasTable('users'));
        $this->withToken($response->json('data.token'))->getJson('/api/v1/religious/account/profile')->assertOk()->assertJsonPath('data.name', 'قارئ');
        $this->withToken($response->json('data.token'))->postJson('/api/v1/quran/sync', ['operations' => []])->assertOk()->assertJsonPath('data.revision', 0);
        $this->assertSame(1, DB::table('personal_access_tokens')->count());
    }

    public function test_idempotence_conflict_tombstone_and_account_isolation(): void
    {
        $sync = app(QuranSync::class);
        $owner = $this->owner();
        $other = $this->owner('other@example.test');
        $add = $this->op();
        $first = $sync->exchange($owner, [$add]);
        $this->assertSame(1, $first['revision']);
        $this->assertSame(1, $sync->exchange($owner, [$add])['revision']);
        $delete = $this->op(revision: 1, deleted: true);
        $removed = $sync->exchange($owner, [$delete]);
        $this->assertTrue($removed['entities']->{'favorite:2:3'}['deleted']);
        $stale = $sync->exchange($owner, [$this->op()]);
        $this->assertSame(['favorite:2:3'], $stale['conflicts']);
        $this->assertSame(2, $stale['revision']);
        $this->assertTrue($stale['entities']->{'favorite:2:3'}['deleted']);
        $this->assertSame([], (array) $sync->exchange($other, [])['entities']);
        $restored = $sync->exchange($owner, [$this->op(revision: 2)]);
        $this->assertFalse($restored['entities']->{'favorite:2:3'}['deleted']);
        $this->assertSame(3, $restored['revision']);
    }

    public function test_invalid_verse_and_staff_account_are_rejected(): void
    {
        $this->postJson('/api/v1/quran/sync', ['operations' => []])->assertUnauthorized();
        $this->actingAs(new User(['name' => 'موظف']), 'sanctum');
        $this->postJson('/api/v1/quran/sync', ['operations' => []])->assertForbidden();
        Sanctum::actingAs($this->owner(), ['quran:sync']);
        $this->postJson('/api/v1/quran/sync', ['operations' => [$this->op('favorite:1:8')]])->assertUnprocessable();
        $this->assertSame(0, DB::table('quran_sync_entities')->count());
    }

    public function test_server_revision_arbitrates_reading_and_preferences(): void
    {
        $sync = app(QuranSync::class);
        $owner = $this->owner();
        $reading = $this->op('reading');
        $reading['value'] = ['lastKey' => '2:3', 'page' => 2, 'alignment' => .2];
        $sync->exchange($owner, [$reading]);
        $old = $reading;
        $old['id'] = (string) Str::uuid();
        $old['value']['lastKey'] = '2:4';
        $response = $sync->exchange($owner, [$old]);
        $this->assertSame('2:3', $response['entities']->reading['value']['lastKey']);
        $this->assertSame(['reading'], $response['conflicts']);
    }
}
