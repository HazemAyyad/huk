<?php

namespace App\Services\Association;

use App\Models\TelegramUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AssociationLegacyIdentityGraph
{
    public function __construct(private AssociationIdentities $identities) {}

    /** Validate the final family graph inside the same transaction as the write. */
    public function update(TelegramUser $parent, array $input): array
    {
        $members = $parent->familyMembers()->lockForUpdate()->get()->keyBy('id');
        $deleted = array_map('intval', $input['delete_members'] ?? []);
        if (array_diff($deleted, $members->keys()->all())) { throw ValidationException::withMessages(['delete_members' => 'أحد أفراد الأسرة لا ينتمي لهذا الملف.']); }
        $root = array_key_exists('national_id', $input) ? $input['national_id'] : $parent->national_id;
        $root = $root === null ? null : $this->identities->normalize($root);
        $proposed = [$root]; $retained = [];
        foreach ($input['family_members'] ?? [] as $id => $member) {
            if (! $members->has((int) $id)) { throw ValidationException::withMessages(['family_members' => 'أحد أفراد الأسرة لا ينتمي لهذا الملف.']); }
            $input['family_members'][$id]['national_id'] = empty($member['national_id']) ? null : $this->identities->normalize($member['national_id']);
        }
        foreach ($members as $id => $member) {
            if (in_array((int) $id, $deleted, true)) { continue; }
            $retained[] = $id;
            $proposed[] = array_key_exists($id, $input['family_members'] ?? []) ? $input['family_members'][$id]['national_id'] : $member->national_id;
        }
        foreach ($input['new_family_members'] ?? [] as $key => $member) {
            $input['new_family_members'][$key]['national_id'] = empty($member['national_id']) ? null : $this->identities->normalize($member['national_id']);
            if (! empty($member['full_name'])) { $proposed[] = $input['new_family_members'][$key]['national_id']; }
        }
        if (array_key_exists('national_id', $input)) { $input['national_id'] = $root; }
        $proposed = array_values(array_filter($proposed, fn ($value) => $value !== null && $value !== ''));
        if (count(array_unique($proposed)) !== count($proposed)) { throw ValidationException::withMessages(['national_id' => 'الهوية مكررة داخل ملف الأسرة.']); }
        $this->identities->lock([$parent->national_id, ...$members->pluck('national_id')->all(), ...$proposed]);
        if (DB::table('telegram_users')->where('id', '!=', $parent->id)->whereIn('national_id', $proposed)->exists()
            || DB::table('family_members')->whereNotIn('id', $retained)->whereIn('national_id', $proposed)->exists()) {
            throw ValidationException::withMessages(['national_id' => 'إحدى الهويات مسجلة في ملف آخر أو محفوظة في سجل الأسرة المحذوف.']);
        }
        return $input;
    }
}
