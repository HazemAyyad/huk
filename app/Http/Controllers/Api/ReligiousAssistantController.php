<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Religious\ReligiousAssistant;
use App\Services\Religious\ReligiousSubscriptions;
use App\Services\Religious\StoreReceipts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ReligiousAssistantController extends Controller
{
    public function capabilities()
    {
        $assistant = app(ReligiousAssistant::class);
        $stores = app(StoreReceipts::class);
        $enabled = $assistant->available();
        $policies = [config('religious_assistant.terms_url'), config('religious_assistant.privacy_url')];
        $canSell = $enabled && collect($policies)->every(fn ($url) => is_string($url) && filter_var($url, FILTER_VALIDATE_URL) && parse_url($url, PHP_URL_SCHEME) === 'https' && parse_url($url, PHP_URL_USER) === null);

        return response()->json(['data' => ['schema' => 1, 'schema_ready' => app(ReligiousSubscriptions::class)->schemaReady(), 'enabled' => $enabled,
            'daily_limit' => config('religious_assistant.daily_limit'), 'monthly_limit' => config('religious_assistant.monthly_limit'), 'question_limit' => 600,
            'policy' => ['terms' => $policies[0], 'privacy' => $policies[1]],
            'stores' => collect(['android', 'ios'])->mapWithKeys(fn ($platform) => [$platform => ['enabled' => $canSell && $stores->ready($platform), 'product' => $canSell && $stores->ready($platform) ? config('religious_assistant.'.$platform.'.product') : null]])->all(),
        ]]);
    }

    public function billing(Request $request)
    {
        $service = app(ReligiousSubscriptions::class);

        return response()->json(['data' => ['store_token' => $service->storeToken($request->user()), 'subscription' => $service->status($request->user()), 'quota' => app(ReligiousAssistant::class)->quota($request->user()->id)]]);
    }

    public function verify(Request $request)
    {
        $data = $request->validate(['platform' => 'required|in:android,ios', 'proof' => 'required|string|max:6000']);

        return response()->json(['data' => app(ReligiousSubscriptions::class)->verify($request->user(), $data['platform'], $data['proof'])]);
    }

    public function ask(Request $request)
    {
        $request->merge(['question' => trim((string) $request->input('question'))]);
        $data = $request->validate(['operation_id' => 'required|uuid', 'question' => 'required|string|max:600']);

        return response()->json(['data' => app(ReligiousAssistant::class)->ask($request->user(), $data['operation_id'], $data['question'])]);
    }

    public function history(Request $request)
    {
        $owner = app(ReligiousSubscriptions::class)->actor($request->user());
        $data = $request->validate(['cursor' => 'sometimes|integer|min:1']);
        $query = DB::table('religious_assistant_requests')->where('account_id', $owner)->where('hidden', false);
        if (isset($data['cursor'])) {
            $query->where('id', '<', $data['cursor']);
        }
        $rows = $query->orderByDesc('id')->limit(21)->get();

        return response()->json(['data' => ['items' => $rows->take(20)->map(fn ($row) => app(ReligiousAssistant::class)->present($row))->values()->all(), 'next_cursor' => $rows->count() > 20 ? $rows[19]->id : null]]);
    }

    public function receipt(Request $request, string $operation)
    {
        $owner = app(ReligiousSubscriptions::class)->actor($request->user());
        $row = DB::table('religious_assistant_requests')->where('account_id', $owner)->where('operation_id', $operation)->first();
        abort_unless($row, 404);

        return response()->json(['data' => app(ReligiousAssistant::class)->present($row)]);
    }

    public function erase(Request $request, int $entry)
    {
        $owner = app(ReligiousSubscriptions::class)->actor($request->user());
        $changed = DB::table('religious_assistant_requests')->where('id', $entry)->where('account_id', $owner)->update(['hidden' => true, 'question' => null, 'answer' => null, 'sources' => '[]', 'updated_at' => now()]);
        abort_unless($changed, 404);

        return response()->json(['data' => ['deleted' => true]]);
    }
}
