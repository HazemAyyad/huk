<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppAccount;
use App\Services\Association\AssociationAccess;
use App\Services\Association\AssociationDevices;
use App\Services\Association\AssociationOperations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class AssociationDevicesController extends Controller
{
    public function register(Request $request, AssociationDevices $devices)
    {
        return response()->json(['data' => [...$devices->register($request->user(), $request->user()->currentAccessToken(), $request->all()),
            'push_ready' => app(\App\Services\Association\AssociationPush::class)->ready()]]);
    }

    public function heartbeat(Request $request, AssociationDevices $devices)
    {
        $devices->seen($request->user());
        return response()->json(['data' => ['seen_at' => now()->toIso8601String()]]);
    }

    public function index(Request $request, AssociationAccess $access)
    {
        $grant = $access->require($request->user(), 'sessions');
        $data = $request->validate(['q' => 'nullable|string|max:120', 'account_id' => 'nullable|integer|min:1', 'page' => 'nullable|integer|min:1|max:100000', 'status' => 'nullable|in:active,revoked']);
        $query = $this->query();
        if (isset($data['account_id'])) { $query->where('account_id', $data['account_id']); }
        if (($data['status'] ?? null) === 'active') { $query->whereNull('association_devices.revoked_at'); }
        if (($data['status'] ?? null) === 'revoked') { $query->whereNotNull('association_devices.revoked_at'); }
        if (! empty($data['q'])) {
            $search = '%'.str_replace(['%', '_'], ['\%', '\_'], $data['q']).'%';
            $query->where(fn ($q) => $q->where('users.name', 'like', $search)->orWhere('religious_accounts.name', 'like', $search)->orWhere('association_devices.label', 'like', $search));
        }
        $page = $query->orderByDesc('association_devices.id')->paginate(30);

        return response()->json(['data' => ['items' => array_map($this->describe(...), $page->items()), 'page' => $page->currentPage(), 'has_more' => $page->hasMorePages(), 'total' => $page->total(),
            'can_revoke' => $access->allows($grant, 'sessions', 'revoke'), 'title' => 'الأجهزة والجلسات']])->header('Cache-Control', 'no-store');
    }

    private function query()
    {
        return DB::table('association_devices')->join('app_accounts', 'app_accounts.id', '=', 'association_devices.account_id')
            ->leftJoin('users', 'users.id', '=', 'app_accounts.user_id')->leftJoin('religious_accounts', 'religious_accounts.id', '=', 'app_accounts.religious_account_id')
            ->select('association_devices.id', 'association_devices.account_id', 'association_devices.label', 'association_devices.platform', 'association_devices.last_seen_at', 'association_devices.revoked_at', 'association_devices.created_at',
                'app_accounts.active', 'app_accounts.association_enabled', 'app_accounts.religious_enabled')
            ->selectRaw('COALESCE(users.name, religious_accounts.name) as name')
            ->selectRaw('CASE WHEN association_devices.fcm_token IS NULL THEN 0 ELSE 1 END as push_registered');
    }

    private function describe(object $device): array
    {
        $data = (array) $device;
        foreach (['last_seen_at', 'revoked_at', 'created_at'] as $key) {
            if ($data[$key]) { $data[$key] = \Carbon\CarbonImmutable::parse($data[$key], config('app.timezone'))->toIso8601String(); }
        }
        foreach (['active', 'association_enabled', 'religious_enabled', 'push_registered'] as $key) { $data[$key] = (bool) $data[$key]; }
        return $data;
    }

    public function show(Request $request, int $id, AssociationAccess $access, AssociationDevices $devices)
    {
        $grant = $access->require($request->user(), 'sessions');
        $device = $this->query()->where('association_devices.id', $id)->first() ?? abort(404);
        $account = AppAccount::findOrFail($device->account_id);
        $tokens = DB::table('association_device_tokens')->join('personal_access_tokens', 'personal_access_tokens.id', '=', 'association_device_tokens.token_id')->where('device_id', $id)->count();
        return response()->json(['data' => ['record' => $this->describe($device), 'active_sessions' => $tokens,
            'device_limit' => $devices->limit($account), 'can_revoke' => $access->allows($grant, 'sessions', 'revoke')]])->header('Cache-Control', 'no-store');
    }

    public function revoke(Request $request, int $id, AssociationAccess $access, AssociationOperations $operations, AssociationDevices $devices)
    {
        $grant = $access->require($request->user(), 'sessions', 'revoke');
        $data = $request->validate(['operation_id' => 'required|uuid', 'account_id' => 'required|integer|min:1', 'reason' => 'required|string|min:3|max:2000']);
        $result = $operations->run($request->user(), 'sessions.'.$id.'.revoke', $data, function () use ($request, $id, $grant, $data, $operations, $devices): array {
            $device = DB::table('association_devices')->where('id', $id)->where('account_id', $data['account_id'])->lockForUpdate()->first() ?? abort(404);
            if ($device->revoked_at) { return ['id' => $id, 'revoked' => true, 'already_applied' => true]; }
            DB::table('association_devices')->where('id', $id)->update(['revoked_at' => now(), 'fcm_token' => null, 'fcm_hash' => null, 'updated_at' => now()]);
            $devices->revokeTokens(AppAccount::findOrFail($device->account_id), $id);
            $operations->audit($request->user(), $grant, 'sessions', $id, 'revoke', ['account_id' => $device->account_id, 'label' => $device->label], null, $data['reason']);

            return ['id' => $id, 'revoked' => true];
        });

        return response()->json(['data' => $result]);
    }
}
