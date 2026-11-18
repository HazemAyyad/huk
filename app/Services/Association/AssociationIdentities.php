<?php

namespace App\Services\Association;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

final class AssociationIdentities
{
    public function normalize(string $identity): string
    {
        return trim(strtr($identity, array_combine(preg_split('//u', '٠١٢٣٤٥٦٧٨٩۰۱۲۳۴۵۶۷۸۹', -1, PREG_SPLIT_NO_EMPTY), str_split('01234567890123456789'))));
    }

    /** Call inside the same transaction as the eventual identity write. */
    public function lock(array $identities): void
    {
        if (! Schema::hasTable('association_identity_locks')) {
            return;
        }
        $keys = array_unique(array_map(fn ($id) => hash('sha256', $this->normalize((string) $id)), array_filter($identities, fn ($id) => $id !== null && trim((string) $id) !== '')));
        sort($keys);
        foreach ($keys as $key) {
            DB::table('association_identity_locks')->insertOrIgnore(['identity_key' => $key, 'created_at' => now()]);
            DB::table('association_identity_locks')->where('identity_key', $key)->lockForUpdate()->first();
        }
    }

    public function available(string $identity, ?int $beneficiary = null, ?int $member = null): void
    {
        $identity = $this->normalize($identity);
        if ($identity === '') {
            return;
        }
        $users = DB::table('telegram_users')->where('national_id', $identity);
        $members = DB::table('family_members')->where('national_id', $identity);
        if ($beneficiary !== null) {
            $users->where('id', '!=', $beneficiary);
        }
        if ($member !== null) {
            $members->where('id', '!=', $member);
        }
        if ($users->exists() || $members->exists()) {
            throw ValidationException::withMessages(['national_id' => 'رقم الهوية مسجل مسبقًا لمستفيد أو فرد أسرة.']);
        }
    }
}
