<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Religious\ReligiousModuleSync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

final class ReligiousModuleController extends Controller
{
    public function capabilities()
    {
        $enabled = config('religious_modules.sync_enabled', false);

        return response()->json(['data' => [
            'schema_version' => config('religious_modules.schema_version', 3),
            'personal_sync' => $enabled,
            'activity_sync' => $enabled,
            'max_batch' => 250,
            'activity_page_size' => 500,
            'entity_kinds' => ['group', 'dhikr', 'session', 'habit', 'khatma', 'tasbih'],
            'event_kinds' => ['count', 'undo', 'complete', 'reset', 'habit', 'quranPage', 'breathing'],
        ]]);
    }

    private function ensureReady(): void
    {
        abort_unless(config('religious_modules.sync_enabled', false), 503, 'Religious module sync is not enabled.');
        foreach (['religious_module_heads', 'religious_personal_entities', 'religious_personal_operations', 'religious_activity_events'] as $table) {
            abort_unless(Schema::hasTable($table), 503, 'Religious module schema is not available.');
        }
    }

    public function personal(Request $request, ReligiousModuleSync $sync)
    {
        $this->ensureReady();
        $input = $request->validate(['operations' => 'present|array|max:250',
            'operations.*' => 'array:id,key,base_revision,deleted,value',
            'operations.*.id' => 'required|uuid|distinct', 'operations.*.key' => 'required|string|max:64',
            'operations.*.base_revision' => 'required|integer|min:0', 'operations.*.deleted' => 'required|boolean',
            'operations.*.value' => 'present']);

        return response()->json(['data' => $sync->personal($request->user(), $input['operations'])]);
    }

    public function activity(Request $request, ReligiousModuleSync $sync)
    {
        $this->ensureReady();
        $input = $request->validate(['events' => 'present|array|max:250', 'cursor' => 'required|integer|min:0', 'events.*' => 'array']);

        return response()->json(['data' => $sync->activity($request->user(), $input['events'], (int) $input['cursor'])]);
    }
}
