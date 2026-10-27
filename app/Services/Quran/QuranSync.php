<?php

namespace App\Services\Quran;

use App\Models\ReligiousAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Server revisions, never device clocks, arbitrate conflicts. Tombstones persist. */
final class QuranSync
{
    public function __construct(private QuranCatalog $catalog) {}

    public function validateOperation(array $op): void
    {
        $key = $op['key'];
        $value = $op['value'] ?? null;
        $valid = false;
        if (preg_match('/^(favorite|bookmark):(\d+):(\d+)$/D', $key, $parts)) {
            $valid = collect($this->catalog->verses())->contains(fn ($v) => $v['key'] === "{$parts[2]}:{$parts[3]}")
                && ($op['deleted'] || is_string($value) && strlen($value) <= 50);
        } elseif (preg_match('/^(surah|juz):(\d+)$/D', $key, $parts)) {
            $valid = (int) $parts[2] >= 1 && (int) $parts[2] <= ($parts[1] === 'surah' ? 114 : 30)
                && ($op['deleted'] || $value === true);
        } elseif ($key === 'reading') {
            $valid = is_array($value) && isset($value['lastKey'])
                && collect($this->catalog->verses())->contains(fn ($v) => $v['key'] === $value['lastKey'])
                && is_numeric($value['alignment'] ?? null) && $value['alignment'] >= 0 && $value['alignment'] <= .9
                && is_int($value['page'] ?? null) && $value['page'] >= 1 && $value['page'] <= 604 && ! $op['deleted'];
        } elseif ($key === 'preferences') {
            $valid = is_array($value) && in_array($value['mode'] ?? null, ['continuous', 'ayah', 'pages'], true)
                && is_numeric($value['fontSize'] ?? null) && $value['fontSize'] >= 18 && $value['fontSize'] <= 36
                && in_array($value['paper'] ?? null, ['light', 'ivory', 'green', 'dark'], true)
                && (! array_key_exists('font', $value) || in_array($value['font'], ['amiri', 'uthmani', 'scheherazade', 'noto', 'lateef'], true))
                && (! array_key_exists('tajweed', $value) || is_bool($value['tajweed']))
                && ! $op['deleted'];
        }
        if (! $valid) {
            throw ValidationException::withMessages(['operations' => 'Invalid Quran entity or value.']);
        }
    }

    public function exchange(ReligiousAccount $account, array $operations): array
    {
        foreach ($operations as $op) {
            $this->validateOperation($op);
        }

        return DB::transaction(function () use ($account, $operations) {
            $owner = ReligiousAccount::query()->lockForUpdate()->findOrFail($account->id);
            $acknowledged = $conflicts = [];
            foreach ($operations as $op) {
                $replay = DB::table('quran_sync_operations')->where('religious_account_id', $owner->id)->where('operation_id', $op['id'])->first();
                if ($replay) {
                    $acknowledged[] = $op['id'];
                    if ($replay->conflicted) {
                        $conflicts[] = $op['key'];
                    }

                    continue;
                }
                $entity = DB::table('quran_sync_entities')->where('religious_account_id', $owner->id)->where('entity_key', $op['key'])->first();
                $conflict = (int) ($entity->revision ?? 0) !== (int) $op['base_revision'];
                if (! $conflict) {
                    $owner->quran_revision++;
                    DB::table('quran_sync_entities')->updateOrInsert(
                        ['religious_account_id' => $owner->id, 'entity_key' => $op['key']],
                        ['value' => $op['deleted'] ? null : json_encode($op['value'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                            'deleted' => $op['deleted'], 'revision' => $owner->quran_revision]);
                } else {
                    $conflicts[] = $op['key'];
                }
                DB::table('quran_sync_operations')->insert(['religious_account_id' => $owner->id, 'operation_id' => $op['id'], 'conflicted' => $conflict]);
                $acknowledged[] = $op['id'];
            }
            $owner->save();

            return ['revision' => $owner->quran_revision, 'acknowledged' => $acknowledged, 'conflicts' => array_values(array_unique($conflicts)),
                'entities' => (object) DB::table('quran_sync_entities')->where('religious_account_id', $owner->id)->get()->mapWithKeys(fn ($row) => [$row->entity_key => [
                    'value' => $row->value === null ? null : json_decode($row->value, true, flags: JSON_THROW_ON_ERROR),
                    'deleted' => (bool) $row->deleted, 'revision' => (int) $row->revision]])->all()];
        }, 3);
    }
}
