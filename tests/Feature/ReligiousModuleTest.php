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
        $this->getJson('/api/v1/religious/capabilities')->assertOk()->assertJsonPath('data.personal_sync', true)
            ->assertJsonPath('data.schema_version', 3)->assertJsonFragment(['tasbih']);
        $this->postJson('/api/v1/religious/personal/sync', ['operations' => []])->assertUnauthorized();
        Sanctum::actingAs($this->owner, ['quran:sync']);
        config(['religious_modules.sync_enabled' => false]);
        $this->getJson('/api/v1/religious/capabilities')->assertJsonPath('data.personal_sync', false);
        $this->postJson('/api/v1/religious/personal/sync', ['operations' => []])->assertStatus(503);
        $this->assertSame(0, DB::table('religious_personal_entities')->count());
    }

    public function test_tasbih_contract_roundtrips_all_six_styles_and_rejects_invalid_goals(): void
    {
        foreach (['buttons', 'fullscreen', 'circular', 'horizontal', 'classic', 'digital'] as $index => $style) {
            $id = (string) Str::uuid();
            $value = ['id' => $id, 'text' => 'سبحان الله', 'benefit' => 'ملاحظة شخصية', 'goal' => 33,
                'epoch' => (string) Str::uuid(), 'style' => $style, 'sort_order' => 0];
            $op = ['id' => (string) Str::uuid(), 'key' => 'tasbih:'.$id, 'base_revision' => 0, 'deleted' => false, 'value' => $value];
            $response = $this->sync->personal($this->owner, [$op]);
            $this->assertSame($value, $response['entities']->{$op['key']}['value']);
            $this->assertSame($response['revision'], $this->sync->personal($this->owner, [$op])['revision']);
            foreach ([['goal' => '33'], ['goal' => 0], ['goal' => 1000001], ['style' => 'invented']] as $invalid) {
                try {
                    $this->sync->entity($op['key'], [...$value, ...$invalid]);
                    $this->fail('Expected validation failure');
                } catch (ValidationException) {
                    $this->assertSame($index + 1, DB::table('religious_personal_entities')->count());
                }
            }
        }
    }

    public function test_progress_extensions_keep_legacy_receipts_and_roundtrip_typed_fields(): void
    {
        foreach (['habit', 'khatma'] as $kind) {
            $id = (string) Str::uuid();
            $legacy = $kind === 'habit'
                ? ['id' => $id, 'title' => 'عادة', 'weekdays' => [7, 1], 'target' => 3, 'enabled' => true]
                : ['id' => $id, 'title' => 'ختمة', 'start_date' => '2026-10-25', 'end_date' => '2026-11-23', 'first_page' => 1, 'last_page' => 604];
            $op = ['id' => (string) Str::uuid(), 'key' => $kind.':'.$id, 'base_revision' => 0, 'deleted' => false, 'value' => $legacy];
            $first = $this->sync->personal($this->owner, [$op]);
            $revision = $first['entities']->{$op['key']}['revision'];
            $this->assertSame($legacy, $first['entities']->{$op['key']}['value']);
            $extra = $kind === 'habit'
                ? ['epoch' => (string) Str::uuid(), 'icon' => 'book']
                : ['epoch' => (string) Str::uuid(), 'archived' => false, 'reminder_minutes' => 1245];
            $updated = [...$op, 'id' => (string) Str::uuid(), 'base_revision' => $revision, 'value' => [...$legacy, ...$extra]];
            $result = $this->sync->personal($this->owner, [$updated]);
            $this->assertEquals($updated['value'], $result['entities']->{$op['key']}['value']);
            $this->assertSame($result['revision'], $this->sync->personal($this->owner, [$op])['revision']);
            $invalids = $kind === 'habit'
                ? [['icon' => 'invented'], ['epoch' => null], ['weekdays' => []], ['weekdays' => [1, 1]]]
                : [['reminder_minutes' => 1440], ['reminder_minutes' => '1245'], ['archived' => 'false'], ['epoch' => null]];
            foreach ($invalids as $invalid) {
                try {
                    $this->sync->entity($op['key'], [...$updated['value'], ...$invalid]);
                    $this->fail('Expected typed progress validation failure');
                } catch (ValidationException) {
                    $this->assertSame($result['revision'], $this->sync->personal($this->owner, [])['revision']);
                }
            }
        }
    }

    public function test_progress_events_replay_two_devices_days_undo_and_owner_isolation(): void
    {
        $session = (string) Str::uuid();
        $epoch = (string) Str::uuid();
        $page = [...$this->event(), 'session_id' => $session, 'epoch' => $epoch, 'subject_id' => 'quran-page:1', 'kind' => 'quranPage'];
        $secondDevice = [...$page, 'id' => (string) Str::uuid(), 'device_id' => (string) Str::uuid(), 'day' => '2026-10-25', 'occurred_at' => '2026-10-25T08:30:00.123Z'];
        $habit = [...$page, 'id' => (string) Str::uuid(), 'subject_id' => 'habit:'.$session.':1', 'kind' => 'habit'];
        $undo = [...$habit, 'id' => (string) Str::uuid(), 'kind' => 'undo', 'amount' => 0, 'reverses' => $habit['id']];
        $result = $this->sync->activity($this->owner, [$page, $secondDevice, $habit, $undo], 0);
        $this->assertSame(4, $result['revision']);
        $this->assertSame(4, $this->sync->activity($this->owner, [$page, $secondDevice, $habit, $undo], 0)['revision']);
        $this->assertSame(['2026-10-24', '2026-10-25', '2026-10-24', '2026-10-24'], array_column(array_column($result['events'], 'event'), 'day'));
        $other = ReligiousAccount::create(['name' => 'Other', 'email' => 'progress-other@example.test', 'password' => 'test-password']);
        $this->assertSame([], $this->sync->activity($other, [], 0)['events']);
        $this->assertSame(7, (int) $this->owner->fresh()->quran_revision);
        $this->assertSame(9, (int) $this->owner->fresh()->content_revision);
    }

    public function test_breathing_duration_and_completion_are_distinct_and_replay_once(): void
    {
        $session = (string) Str::uuid();
        $duration = [...$this->event(), 'session_id' => $session, 'epoch' => $session,
            'kind' => 'breathing', 'subject_id' => 'breathing:peace', 'amount' => 60];
        $complete = [...$duration, 'id' => (string) Str::uuid(), 'kind' => 'complete', 'amount' => 0];
        $partialId = (string) Str::uuid();
        $partial = [...$duration, 'id' => (string) Str::uuid(), 'session_id' => $partialId,
            'epoch' => $partialId, 'subject_id' => 'breathing:gratitude', 'amount' => 5];
        $result = $this->sync->activity($this->owner, [$duration, $complete, $partial], 0);
        $this->assertSame(3, $result['revision']);
        $this->assertSame(3, $this->sync->activity($this->owner, [$duration, $complete, $partial], 0)['revision']);
        $this->assertSame(65, (int) DB::table('religious_activity_events')->where('kind', 'breathing')->sum('amount'));
        $this->assertSame(1, DB::table('religious_activity_events')->where('kind', 'complete')->count());
        $other = ReligiousAccount::create(['name' => 'Other', 'email' => 'breathing-other@example.test', 'password' => 'test-password']);
        $this->assertSame([], $this->sync->activity($other, [], 0)['events']);
    }

    public function test_tasbih_reset_and_replay_preserve_previous_count_history(): void
    {
        $tap = [...$this->event(), 'subject_id' => 'tasbih:test:33'];
        $reset = [...$tap, 'id' => (string) Str::uuid(), 'kind' => 'reset', 'amount' => 0];
        $next = [...$tap, 'id' => (string) Str::uuid()];
        $result = $this->sync->activity($this->owner, [$tap, $reset, $next], 0);
        $this->assertSame(['count', 'reset', 'count'], array_column(array_column($result['events'], 'event'), 'kind'));
        $this->assertSame(3, $this->sync->activity($this->owner, [$tap, $reset, $next], 0)['revision']);
        $this->assertSame(3, DB::table('religious_activity_events')->count());
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

    public function test_preface_extension_preserves_an_existing_p02_receipt_hash(): void
    {
        $op = $this->operation();
        DB::table('religious_module_heads')->insert(['religious_account_id' => $this->owner->id, 'stream' => 'personal', 'revision' => 1]);
        DB::table('religious_personal_entities')->insert(['religious_account_id' => $this->owner->id, 'entity_key' => $op['key'],
            'value' => json_encode($op['value'], JSON_THROW_ON_ERROR), 'deleted' => false, 'revision' => 1]);
        DB::table('religious_personal_operations')->insert(['religious_account_id' => $this->owner->id, 'operation_id' => $op['id'],
            'entity_key' => $op['key'], 'payload_hash' => hash('sha256', json_encode($op, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)), 'conflicted' => false]);
        $this->assertSame(1, $this->sync->personal($this->owner, [$op])['revision']);
        $next = [...$op, 'id' => (string) Str::uuid(), 'base_revision' => 1, 'value' => [...$op['value'], 'preface' => 'قبل الذكر']];
        $response = $this->sync->personal($this->owner, [$next]);
        $this->assertSame('قبل الذكر', $response['entities']->{$op['key']}['value']['preface']);
        $this->assertSame(2, $this->sync->personal($this->owner, [$op])['revision']);
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
