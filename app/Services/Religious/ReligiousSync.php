<?php

namespace App\Services\Religious;

use App\Models\ReligiousAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** The existing religious account owns this independent revision stream. */
final class ReligiousSync
{
    public function __construct(private readonly ReligiousCatalog $catalog) {}

    public function validateOperation(array $op): void
    {
        if (! preg_match('/^(favorite|adhkar|queue|playback):([a-z0-9][a-z0-9_-]{0,95})$/D', $op['key'], $parts)) {
            throw ValidationException::withMessages(['operations' => 'Invalid religious content key.']);
        }
        $item = $this->catalog->item($parts[2], ! $op['deleted']);
        $value = $op['value'] ?? null;
        $valid = $item !== null && ($op['deleted'] || $value === true);
        if ($parts[1] === 'playback' && ! $op['deleted']) {
            $count = $this->trackCount($item ?? []);
            $valid = $item !== null && is_array($value) && count($value) === 2 &&
                is_int($value['seconds'] ?? null) && $value['seconds'] >= 0 && $value['seconds'] <= 86400 &&
                is_int($value['index'] ?? null) && $value['index'] >= 0 && $value['index'] < $count;
        }
        if (! $valid) {
            throw ValidationException::withMessages(['operations' => 'Invalid religious content or playback value.']);
        }
    }

    private function trackCount(array $item): int
    {
        if (($item['type'] ?? '') === 'playlist') {
            return array_sum(array_map(fn ($id) => $this->trackCount($this->catalog->item($id) ?? []), $item['related'] ?? []));
        }

        return empty($item['audioUrl']) ? count($item['verseKeys'] ?? []) : 1;
    }

    public function exchange(ReligiousAccount $account, array $operations): array
    {
        foreach ($operations as $op) {
            $this->validateOperation($op);
        }

        return DB::transaction(function () use ($account, $operations): array {
            $owner = ReligiousAccount::query()->lockForUpdate()->findOrFail($account->id);
            $revision = (int) $owner->content_revision;
            $acknowledged = $conflicts = [];
            foreach ($operations as $op) {
                $replay = DB::table('religious_content_sync_operations')->where('religious_account_id', $owner->id)
                    ->where('operation_id', $op['id'])->first();
                if ($replay) {
                    $acknowledged[] = $op['id'];
                    if ($replay->conflicted) {
                        $conflicts[] = $op['key'];
                    }

                    continue;
                }
                $entity = DB::table('religious_content_sync_entities')->where('religious_account_id', $owner->id)
                    ->where('entity_key', $op['key'])->first();
                $conflict = (int) ($entity->revision ?? 0) !== $op['base_revision'];
                if (! $conflict) {
                    $revision++;
                    DB::table('religious_content_sync_entities')->updateOrInsert(
                        ['religious_account_id' => $owner->id, 'entity_key' => $op['key']],
                        ['value' => $op['deleted'] ? null : json_encode($op['value'], JSON_THROW_ON_ERROR),
                            'deleted' => $op['deleted'], 'revision' => $revision]);
                } else {
                    $conflicts[] = $op['key'];
                }
                DB::table('religious_content_sync_operations')->insert(['religious_account_id' => $owner->id,
                    'operation_id' => $op['id'], 'conflicted' => $conflict]);
                $acknowledged[] = $op['id'];
            }
            $owner->content_revision = $revision;
            $owner->save();

            return ['revision' => $revision, 'acknowledged' => $acknowledged,
                'conflicts' => array_values(array_unique($conflicts)),
                'entities' => (object) DB::table('religious_content_sync_entities')->where('religious_account_id', $owner->id)
                    ->get()->mapWithKeys(fn ($row) => [$row->entity_key => ['value' => $row->value === null ? null : json_decode($row->value, true),
                        'deleted' => (bool) $row->deleted, 'revision' => (int) $row->revision]])->all()];
        });
    }
}
