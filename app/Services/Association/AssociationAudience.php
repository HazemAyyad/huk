<?php

namespace App\Services\Association;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class AssociationAudience
{
    public function __construct(private AssociationAccess $access) {}

    public function validate(array $criteria, ?object $grant = null): array
    {
        $data = Validator::make($criteria, ['category_ids' => 'sometimes|array|max:100', 'category_ids.*' => 'integer|distinct|exists:categories,id',
            'beneficiary_ids' => 'sometimes|array|max:5000', 'beneficiary_ids.*' => 'integer|distinct|min:1',
            'governorate' => 'sometimes|nullable|string|max:255', 'city' => 'sometimes|nullable|string|max:255', 'gender' => 'sometimes|nullable|in:male,female',
            'age_min' => 'sometimes|nullable|integer|min:0|max:150', 'age_max' => 'sometimes|nullable|integer|min:0|max:150',
            'include_flagged_ids' => 'sometimes|array|max:5000', 'include_flagged_ids.*' => 'integer|distinct|min:1'])->validate();
        abort_if(array_diff(array_keys($criteria), array_keys($data)), 422, 'unsupported_audience_filter');
        abort_if(isset($data['age_min'], $data['age_max']) && $data['age_min'] > $data['age_max'], 422, 'invalid_age_range');
        if ($grant) {
            abort_if(! $this->access->global($grant) && array_diff($data['category_ids'] ?? [], $this->access->categories($grant)), 403, 'category_outside_scope');
            foreach (['beneficiary_ids', 'include_flagged_ids'] as $key) {
                if (! empty($data[$key])) {
                    abort_unless($this->access->beneficiaryQuery($grant)->whereIn('telegram_users.id', $data[$key])->count() === count($data[$key]), 422, 'audience_outside_scope');
                }
            }
        }
        foreach (['category_ids', 'beneficiary_ids', 'include_flagged_ids'] as $key) { if (isset($data[$key])) { sort($data[$key], SORT_NUMERIC); } }
        ksort($data);

        return $data;
    }

    public function query(array $criteria, ?object $grant = null, bool $excludeFlagged = false)
    {
        $query = $grant ? $this->access->beneficiaryQuery($grant) : DB::table('telegram_users')->whereNull('telegram_users.deleted_at');
        $query->whereNotIn('telegram_users.id', DB::table('association_deleted_records')->where('module', 'beneficiaries')->select('record_id'));
        if (! empty($criteria['category_ids'])) {
            $query->where(function ($query) use ($criteria): void {
                $query->whereIn('telegram_users.category_id', $criteria['category_ids'])
                    ->orWhereIn('telegram_users.id', DB::table('user_categories')->whereIn('category_id', $criteria['category_ids'])->select('telegram_user_id'));
            });
        }
        if (! empty($criteria['beneficiary_ids'])) { $query->whereIn('telegram_users.id', $criteria['beneficiary_ids']); }
        foreach (['governorate', 'city', 'gender'] as $field) { if (! empty($criteria[$field])) { $query->where('telegram_users.'.$field, $criteria[$field]); } }
        if (isset($criteria['age_min'])) { $query->whereDate('telegram_users.birth_date', '<=', now()->subYears($criteria['age_min'])->toDateString()); }
        if (isset($criteria['age_max'])) { $query->whereDate('telegram_users.birth_date', '>', now()->subYears($criteria['age_max'] + 1)->toDateString()); }
        if ($excludeFlagged) {
            $query->where(function ($query) use ($criteria): void {
                $query->where(function ($query): void {
                    $query->where('telegram_users.is_blocked', false)->whereNotIn('telegram_users.id', DB::table('telegram_user_notes')->whereNull('deleted_by')
                        ->whereNotIn('id', DB::table('association_deleted_records')->where('module', 'beneficiary-notes')->select('record_id'))->select('telegram_user_id'));
                });
                if (! empty($criteria['include_flagged_ids'])) { $query->orWhereIn('telegram_users.id', $criteria['include_flagged_ids']); }
            });
        }

        return $query;
    }

    public function fingerprint($query): string
    {
        $hash = hash_init('sha256');
        foreach ((clone $query)->orderBy('telegram_users.id')->select(['telegram_users.id', 'telegram_users.updated_at'])->cursor() as $row) {
            hash_update($hash, $row->id.'|'.$row->updated_at."\n");
        }

        return hash_final($hash);
    }
}
