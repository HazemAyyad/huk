<?php

namespace App\Services\Association;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

final class AssociationNotificationAudience
{
    public function __construct(private AssociationAudience $audience, private AssociationAccess $access) {}

    public function validate(array $target, object $grant): array
    {
        $data = Validator::make($target, ['kind' => 'required|in:beneficiaries,staff,religious,all', 'filter' => 'sometimes|array',
            'account_ids' => 'sometimes|array|max:5000', 'account_ids.*' => 'integer|distinct|min:1'])->validate();
        abort_if(array_diff(array_keys($target), array_keys($data)), 422, 'unsupported_audience_filter');
        abort_unless($data['kind'] === 'beneficiaries' || $this->access->global($grant), 403, 'global_audience_required');
        $data['filter'] = $this->audience->validate($data['filter'] ?? [], $grant);
        abort_if($data['kind'] !== 'beneficiaries' && $data['filter'] !== [], 422, 'beneficiary_filter_requires_beneficiary_audience');
        if (! empty($data['account_ids'])) {
            $ids = $data['account_ids']; unset($data['account_ids']);
            abort_unless($this->query($data, $grant)->whereIn('app_accounts.id', $ids)->count() === count($ids), 422, 'audience_outside_scope');
            sort($ids, SORT_NUMERIC); $data['account_ids'] = $ids;
        }

        return $data;
    }

    public function query(array $target, object $grant)
    {
        $query = DB::table('app_accounts')->leftJoin('users', 'users.id', '=', 'app_accounts.user_id')
            ->leftJoin('telegram_users', 'telegram_users.id', '=', 'users.telegram_user_id')->where('app_accounts.active', true);
        if (Schema::hasColumn('users', 'deleted_by')) { $query->whereNull('users.deleted_by'); }
        $query->whereNotIn('app_accounts.id', DB::table('association_account_policies')->where('app_enabled', false)->select('account_id'));
        switch ($target['kind']) {
            case 'beneficiaries':
                $query->where('app_accounts.association_enabled', true)->whereIn('telegram_users.id', $this->audience->query($target['filter'], $grant, true)->select('telegram_users.id'));
                break;
            case 'staff':
                $query->where('app_accounts.association_enabled', true)->whereIn('users.id', DB::table('role_user')->where('user_type', (new \App\Models\User)->getMorphClass())->select('user_id'));
                break;
            case 'religious': $query->where('app_accounts.religious_enabled', true); break;
        }
        if (! empty($target['account_ids'])) { $query->whereIn('app_accounts.id', $target['account_ids']); }

        return $query;
    }

    public function fingerprint(array $target, object $grant): string
    {
        $hash = hash_init('sha256');
        foreach ($this->query($target, $grant)->orderBy('app_accounts.id')->select('app_accounts.id', 'app_accounts.updated_at', 'telegram_users.updated_at as beneficiary_updated', 'telegram_users.is_blocked', 'telegram_users.blocked_reason')->cursor() as $row) {
            hash_update($hash, json_encode((array) $row, JSON_THROW_ON_ERROR)."\n");
        }
        if ($target['kind'] === 'beneficiaries') {
            $people = $this->audience->query($target['filter'], $grant)->select('telegram_users.id');
            foreach (DB::table('telegram_user_notes')->whereIn('telegram_user_id', $people)->orderBy('id')->cursor() as $note) {
                hash_update($hash, json_encode((array) $note, JSON_THROW_ON_ERROR)."\n");
            }
        }

        return hash_final($hash);
    }

    public function flagged(array $target, object $grant): array
    {
        if ($target['kind'] !== 'beneficiaries') { return ['count' => 0, 'items' => []]; }
        $query = $this->audience->query($target['filter'], $grant)->where(function ($query): void {
            $query->where('telegram_users.is_blocked', true)->orWhereIn('telegram_users.id', DB::table('telegram_user_notes')->whereNull('deleted_by')
                ->whereNotIn('id', DB::table('association_deleted_records')->where('module', 'beneficiary-notes')->select('record_id'))->select('telegram_user_id'));
        });

        $included = empty($target['filter']['include_flagged_ids']) ? [] : $this->audience->query($target['filter'], $grant)
            ->whereIn('telegram_users.id', $target['filter']['include_flagged_ids'])->orderBy('telegram_users.id')->select('telegram_users.id', 'full_name', 'national_id')
            ->selectRaw('SUBSTR(blocked_reason, 1, 500) as blocked_reason')->get()->all();

        return ['count' => (clone $query)->count(), 'items' => $query->orderBy('telegram_users.id')->limit(50)->get(['telegram_users.id', 'full_name', 'national_id', 'blocked_reason'])->all(), 'included' => $included];
    }
}
