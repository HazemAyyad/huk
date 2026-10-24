<?php

namespace Tests\Feature;

use App\Models\ReligiousAccount;
use App\Services\Religious\ReligiousModuleSync;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/** All schema writes are restricted to this named SQLite in-memory connection. */
final class ReligiousModuleTest extends TestCase
{
    private ReligiousAccount $owner;

    private ReligiousModuleSync $sync;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'religious_module_testing', 'database.connections.religious_module_testing' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ], 'religious_modules.sync_enabled' => true]);
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        Schema::create('religious_accounts', function ($table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->unsignedBigInteger('quran_revision')->default(7);
            $table->unsignedBigInteger('content_revision')->default(9);
            $table->timestamps();
        });
        (require database_path('migrations/2026_10_24_120000_create_religious_module_storage.php'))->up();
        $this->owner = ReligiousAccount::create(['name' => 'Test', 'email' => 'module@example.test', 'password' => 'test-password']);
        $this->sync = app(ReligiousModuleSync::class);
    }

    private function operation(): array
    {
        $id = (string) Str::uuid();

        return ['id' => (string) Str::uuid(), 'key' => 'dhikr:'.$id, 'base_revision' => 0, 'deleted' => false,
            'value' => ['id' => $id, 'text' => 'ذكر شخصي للاختبار', 'target' => 3, 'source' => '']];
    }

    private function event(): array
    {
        return ['id' => (string) Str::uuid(), 'device_id' => (string) Str::uuid(), 'session_id' => (string) Str::uuid(),
            'subject_id' => 'dhikr:test', 'epoch' => (string) Str::uuid(), 'kind' => 'count', 'amount' => 1,
            'occurred_at' => '2026-10-24T08:30:00.123Z', 'day' => '2026-10-24', 'utc_offset_minutes' => 180, 'reverses' => null];
    }

    public function test_capabilities_auth_and_disabled_service_are_honest(): void
    {
        $this->getJson('/api/v1/religious/capabilities')->assertOk()->assertJsonPath('data.personal_sync', true);
        $this->postJson('/api/v1/religious/personal/sync', ['operations' => []])->assertUnauthorized();
        Sanctum::actingAs($this->owner, ['quran:sync']);
        config(['religious_modules.sync_enabled' => false]);
        $this->getJson('/api/v1/religious/capabilities')->assertJsonPath('data.personal_sync', false);
        $this->postJson('/api/v1/religious/personal/sync', ['operations' => []])->assertStatus(503);
        $this->assertSame(0, DB::table('religious_personal_entities')->count());
    }

    public function test_tokens_need_the_religious_ability_and_missing_api_pages_are_json(): void
    {
        Sanctum::actingAs($this->owner, []);
        $this->postJson('/api/v1/religious/activity/sync', ['events' => [], 'cursor' => 0])->assertForbidden();
        $this->get('/api/v1/religious/unknown-route')->assertNotFound()->assertJsonPath('code', 'not_found');
    }

    public function test_personal_replay_conflict_tombstone_and_owner_isolation(): void
    {
        $op = $this->operation();
        $saved = $this->sync->personal($this->owner, [$op]);
        $this->assertSame(1, $saved['revision']);
        $this->assertSame(1, $this->sync->personal($this->owner, [$op])['revision']);
        $deleted = [...$op, 'id' => (string) Str::uuid(), 'base_revision' => 1, 'deleted' => true, 'value' => null];
        $this->assertSame(2, $this->sync->personal($this->owner, [$deleted])['revision']);
        $stale = $this->sync->personal($this->owner, [[...$op, 'id' => (string) Str::uuid()]]);
        $this->assertSame([$op['key']], $stale['conflicts']);
        $this->assertTrue($stale['entities']->{$op['key']}['deleted']);
        $other = ReligiousAccount::create(['name' => 'Other', 'email' => 'other@example.test', 'password' => 'test-password']);
        $this->assertSame([], (array) $this->sync->personal($other, [])['entities']);
        $this->assertSame(1, $this->sync->personal($other, [$op])['revision']);
        $this->assertSame(7, (int) $this->owner->fresh()->quran_revision);
        $this->assertSame(9, (int) $this->owner->fresh()->content_revision);
    }

    public function test_reused_operation_id_cannot_change_data_and_rolls_back_batch(): void
    {
        $op = $this->operation();
        $this->sync->personal($this->owner, [$op]);
        try {
            $this->sync->personal($this->owner, [$this->operation(), [...$op, 'deleted' => true, 'value' => null]]);
            $this->fail('Expected conflict');
        } catch (HttpException $e) {
            $this->assertSame(409, $e->getStatusCode());
        }
        $this->assertSame(1, DB::table('religious_personal_entities')->count());
        $this->assertSame(1, $this->sync->personal($this->owner, [])['revision']);
    }

    public function test_typed_values_reject_strings_extra_fields_and_normalized_duplicates(): void
    {
        $op = $this->operation();
        foreach ([['target' => '3'], ['target' => 0], ['invented' => true]] as $change) {
            try {
                $this->sync->personal($this->owner, [[...$op, 'value' => [...$op['value'], ...$change]]]);
                $this->fail('Expected validation error');
            } catch (ValidationException) {
                $this->assertSame(0, DB::table('religious_personal_entities')->count());
            }
        }
        $this->expectException(ValidationException::class);
        $this->sync->personal($this->owner, [$op, [...$op, 'id' => strtoupper($op['id'])]]);
    }

    public function test_all_five_entity_contracts_are_accepted(): void
    {
        $values = [
            'group' => ['title' => 'مجموعة', 'item_ids' => ['daily:morning', 'hisn-001'], 'sort_order' => 0],
            'session' => ['group_id' => 'daily:morning', 'day' => '2026-10-24', 'epoch' => (string) Str::uuid(),
                'targets' => ['hisn-001' => 3], 'mode' => 'cards', 'catalog_version' => 'v1', 'auto_advance' => false, 'cursor' => 0],
            'habit' => ['title' => 'عادة', 'weekdays' => [1, 7], 'target' => 1, 'enabled' => true],
            'khatma' => ['title' => 'ختمة', 'start_date' => '2026-10-24', 'end_date' => '2026-11-24', 'first_page' => 1, 'last_page' => 604],
        ];
        $ops = [$this->operation()];
        foreach ($values as $kind => $value) {
            $id = (string) Str::uuid();
            $ops[] = ['id' => (string) Str::uuid(), 'key' => $kind.':'.$id, 'base_revision' => 0, 'deleted' => false, 'value' => ['id' => $id, ...$value]];
        }
        $this->assertSame(5, $this->sync->personal($this->owner, $ops)['revision']);
    }

    public function test_events_replay_undo_once_and_cross_owner_undo_is_rejected(): void
    {
        $event = $this->event();
        $undo = [...$event, 'id' => (string) Str::uuid(), 'kind' => 'undo', 'amount' => 0, 'reverses' => $event['id']];
        $this->assertSame(2, $this->sync->activity($this->owner, [$event, $undo], 0)['revision']);
        $this->assertSame(2, $this->sync->activity($this->owner, [$event, $undo], 0)['revision']);
        try {
            $this->sync->activity($this->owner, [[...$undo, 'id' => (string) Str::uuid()]], 0);
            $this->fail('Expected duplicate undo conflict');
        } catch (HttpException $e) {
            $this->assertSame(409, $e->getStatusCode());
        }
        $other = ReligiousAccount::create(['name' => 'Other', 'email' => 'other@example.test', 'password' => 'test-password']);
        $this->expectException(ValidationException::class);
        $this->sync->activity($other, [$undo], 0);
    }

    public function test_invalid_undo_rolls_back_whole_event_batch(): void
    {
        $event = $this->event();
        try {
            $this->sync->activity($this->owner, [$event, [...$event, 'id' => (string) Str::uuid(), 'kind' => 'undo', 'amount' => 0,
                'session_id' => (string) Str::uuid(), 'reverses' => $event['id']]], 0);
            $this->fail('Expected validation error');
        } catch (ValidationException) {
            $this->assertSame(0, DB::table('religious_activity_events')->count());
            $this->assertSame(0, $this->sync->activity($this->owner, [], 0)['revision']);
        }
    }

    public function test_pagination_keeps_owner_cursor_and_duplicate_event_ids_are_immutable(): void
    {
        $event = $this->event();
        for ($batch = 0; $batch < 3; $batch++) {
            $events = [];
            for ($i = 0; $i < 200; $i++) {
                $events[] = [...$event, 'id' => (string) Str::uuid()];
            }
            $this->sync->activity($this->owner, $events, 0);
        }
        $first = $this->sync->activity($this->owner, [], 0);
        $this->assertSame(500, count($first['events']));
        $this->assertTrue($first['has_more']);
        $next = $this->sync->activity($this->owner, [], $first['cursor']);
        $this->assertSame(100, count($next['events']));
        $this->assertSame(600, $next['cursor']);
        $this->assertFalse($next['has_more']);
        $record = $first['events'][0]['event'];
        try {
            $this->sync->activity($this->owner, [[...$record, 'amount' => 2]], 0);
            $this->fail('Expected immutable event conflict');
        } catch (HttpException $e) {
            $this->assertSame(409, $e->getStatusCode());
        }
        $this->assertSame(600, DB::table('religious_activity_events')->count());
    }
}
