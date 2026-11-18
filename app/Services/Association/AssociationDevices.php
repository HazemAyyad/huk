<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

final class AssociationDevices
{
    public function limit(AppAccount $account): ?int
    {
        if ($account->user_id && User::whereKey($account->user_id)->whereNotNull('telegram_user_id')->where('telegram_user_id', '>', 0)->exists()) { return 1; }
        $limit = DB::table('association_account_policies')->where('account_id', $account->id)->value('device_limit');

        return $limit === null ? null : (int) $limit;
    }

    public function register(AppAccount $account, PersonalAccessToken $token, array $input): array
    {
        $data = Validator::make($input, ['installation_id' => 'required|uuid', 'label' => 'nullable|string|max:150', 'platform' => 'required|in:android,ios,macos,windows,linux,web',
            'fcm_token' => 'sometimes|nullable|string|min:20|max:4096'])->validate();
        return DB::transaction(function () use ($account, $token, $data): array {
            AppAccount::whereKey($account->id)->lockForUpdate()->firstOrFail();
            $policy = DB::table('association_account_policies')->where('account_id', $account->id)->first();
            abort_if($policy && ! $policy->app_enabled, 403, 'application_access_disabled');
            $hash = hash('sha256', mb_strtolower($data['installation_id']));
            $row = DB::table('association_devices')->where('account_id', $account->id)->where('device_hash', $hash)->lockForUpdate()->first();
            if ($row) { abort_if($row->revoked_at, 403, 'device_revoked'); }
            else {
                $limit = $this->limit($account);
                abort_if($limit !== null && DB::table('association_devices')->where('account_id', $account->id)->whereNull('revoked_at')->count() >= $limit, 409, 'device_limit_reached_contact_staff');
            }
            $values = ['label' => $data['label'] ?? null, 'platform' => $data['platform'], 'last_seen_at' => now(), 'updated_at' => now()];
            if (array_key_exists('fcm_token', $data)) {
                $fcmHash = $data['fcm_token'] ? hash('sha256', $data['fcm_token']) : null;
                if ($fcmHash) {
                    // A push registration belongs to the currently signed-in account only.
                    DB::table('association_devices')->where('fcm_hash', $fcmHash)->when($row, fn ($query) => $query->where('id', '!=', $row->id))->update(['fcm_token' => null, 'fcm_hash' => null, 'updated_at' => now()]);
                }
                $values['fcm_token'] = $data['fcm_token'] ? Crypt::encryptString($data['fcm_token']) : null;
                $values['fcm_hash'] = $fcmHash;
            }
            if ($row) { $id = $row->id; DB::table('association_devices')->where('id', $id)->update($values); }
            else { $id = DB::table('association_devices')->insertGetId([...$values, 'account_id' => $account->id, 'device_hash' => $hash, 'created_at' => now()]); }
            DB::table('association_device_tokens')->updateOrInsert(['token_id' => $token->id], ['device_id' => $id]);
            $this->seen($account, $id);

            return ['id' => $id, 'label' => $values['label'], 'platform' => $values['platform'], 'device_limit' => $this->limit($account)];
        }, 3);
    }

    public function valid(AppAccount $account, PersonalAccessToken $token): bool
    {
        if (! Schema::hasTable('association_devices')) { return true; }
        $policy = DB::table('association_account_policies')->where('account_id', $account->id)->first();
        if ($policy && ! $policy->app_enabled) { return false; }
        $binding = DB::table('association_device_tokens')->where('token_id', $token->id)->first();
        if (! $binding) { return ! config('association.enforce_devices') || $this->limit($account) === null; }
        $device = DB::table('association_devices')->where('id', $binding->device_id)->where('account_id', $account->id)->first();
        if (! $device || $device->revoked_at) { return false; }

        return true;
    }

    public function seen(AppAccount $account, ?int $id = null): void
    {
        if (! Schema::hasTable('association_devices')) { return; }
        $id ??= $account->currentAccessToken() ? DB::table('association_device_tokens')->where('token_id', $account->currentAccessToken()->id)->value('device_id') : null;
        if ($id) { DB::table('association_devices')->where('id', $id)->where('account_id', $account->id)->whereNull('revoked_at')->update(['last_seen_at' => now(), 'updated_at' => now()]); }
        DB::table('association_usage_days')->updateOrInsert(['account_id' => $account->id, 'day' => now()->toDateString()], ['last_seen_at' => now()]);
    }

    public function logout(AppAccount $account, PersonalAccessToken $token): void
    {
        if (! Schema::hasTable('association_devices')) { return; }
        $device = DB::table('association_device_tokens')->where('token_id', $token->id)->value('device_id');
        if ($device) {
            DB::table('association_devices')->where('id', $device)->where('account_id', $account->id)->update(['fcm_token' => null, 'fcm_hash' => null, 'updated_at' => now()]);
            $this->revokeTokens($account, $device);
        }
    }

    public function revokeTokens(AppAccount $account, int $device): void
    {
        $ids = DB::table('association_device_tokens')->where('device_id', $device)->pluck('token_id');
        foreach ($ids as $id) { $account->tokens()->where('name', 'app-admin:'.$id)->delete(); }
        $account->tokens()->whereIn('id', $ids)->delete();
        DB::table('association_device_tokens')->where('device_id', $device)->delete();
    }
}
