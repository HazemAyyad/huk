<?php

namespace App\Services\Religious;

use App\Models\ReligiousAccount;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Independent owner-scoped streams; existing Quran/content revisions are untouched. */
final class ReligiousModuleSync
{
    private function require(bool $valid, string $message = 'Invalid religious module data.'): void
    {
        if (! $valid) {
            throw ValidationException::withMessages(['module' => $message]);
        }
    }

    private function fields(array $data, array $keys): void
    {
        $this->require(count($data) === count($keys) && array_diff($keys, array_keys($data)) === []);
    }

    private function uuid(mixed $value): string
    {
        $this->require(is_string($value) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/iD', $value) === 1);

        return strtolower($value);
    }

    private function subject(mixed $value): string
    {
        $this->require(is_string($value) && preg_match('/^[a-z0-9][a-z0-9:_-]{0,119}$/D', $value) === 1);

        return $value;
    }

    private function number(mixed $value, int $min, int $max = 1000000): int
    {
        $this->require(is_int($value) && $value >= $min && $value <= $max);

        return $value;
    }

    private function text(mixed $value, int $max, bool $empty = false): string
    {
        $this->require(is_string($value));
        // Match Dart String.length, including supplementary Unicode characters.
        $this->require(strlen(mb_convert_encoding($value, 'UTF-16LE', 'UTF-8')) / 2 <= $max && ($empty || trim($value) !== ''));

        return trim($value);
    }

    private function boolean(mixed $value): bool
    {
        $this->require(is_bool($value));

        return $value;
    }

    private function date(mixed $value): string
    {
        $this->require(is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value) === 1);
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $this->require($date !== false && $date->format('Y-m-d') === $value);

        return $value;
    }

    private function key(mixed $value): string
    {
        $this->require(is_string($value) && preg_match('/^(group|dhikr|session|habit|khatma):(.+)$/D', $value, $parts) === 1);

        return $parts[1].':'.$this->uuid($parts[2]);
    }

    public function entity(string $key, mixed $value): array
    {
        $this->require(is_array($value));
        [$kind, $id] = explode(':', $key);
        $this->require($this->uuid($value['id'] ?? null) === $id);
        if ($kind === 'group') {
            $this->fields($value, ['id', 'title', 'item_ids', 'sort_order']);
            $items = $value['item_ids'];
            $this->require(is_array($items) && array_is_list($items) && count($items) <= 1000);
            $items = array_map(fn ($v) => $this->subject($v), $items);
            $this->require(count(array_unique($items)) === count($items));

            return ['id' => $id, 'title' => $this->text($value['title'], 160), 'item_ids' => $items, 'sort_order' => $this->number($value['sort_order'], 0)];
        }
        if ($kind === 'dhikr') {
            $this->fields($value, ['id', 'text', 'target', 'source']);

            return ['id' => $id, 'text' => $this->text($value['text'], 10000), 'target' => $this->number($value['target'], 1), 'source' => $this->text($value['source'], 500, true)];
        }
        if ($kind === 'session') {
            $this->fields($value, ['id', 'group_id', 'day', 'epoch', 'targets', 'mode', 'catalog_version', 'auto_advance', 'cursor']);
            $targets = $value['targets'];
            $this->require(is_array($targets) && count($targets) > 0 && count($targets) <= 1000);
            foreach ($targets as $subject => $target) {
                $this->subject((string) $subject);
                $this->number($target, 1);
            }
            $this->require(in_array($value['mode'], ['cards', 'list', 'focus'], true));

            return ['id' => $id, 'group_id' => $this->subject($value['group_id']), 'day' => $this->date($value['day']),
                'epoch' => $this->uuid($value['epoch']), 'targets' => (object) $targets, 'mode' => $value['mode'],
                'catalog_version' => $this->text($value['catalog_version'], 80), 'auto_advance' => $this->boolean($value['auto_advance']),
                'cursor' => $this->number($value['cursor'], 0, count($targets) - 1)];
        }
        if ($kind === 'habit') {
            $this->fields($value, ['id', 'title', 'weekdays', 'target', 'enabled']);
            $days = $value['weekdays'];
            $this->require(is_array($days) && array_is_list($days) && count($days) > 0 && count($days) <= 7);
            foreach ($days as $day) {
                $this->number($day, 1, 7);
            }
            $this->require(count(array_unique($days)) === count($days));

            return ['id' => $id, 'title' => $this->text($value['title'], 160), 'weekdays' => $days,
                'target' => $this->number($value['target'], 1), 'enabled' => $this->boolean($value['enabled'])];
        }
        $this->fields($value, ['id', 'title', 'start_date', 'end_date', 'first_page', 'last_page']);
        $start = $this->date($value['start_date']);
        $end = $this->date($value['end_date']);
        $this->require($end >= $start);
        $first = $this->number($value['first_page'], 1, 604);

        return ['id' => $id, 'title' => $this->text($value['title'], 160), 'start_date' => $start, 'end_date' => $end,
            'first_page' => $first, 'last_page' => $this->number($value['last_page'], $first, 604)];
    }

    private function json(array $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function head(int $owner, string $stream): int
    {
        return (int) (DB::table('religious_module_heads')->where('religious_account_id', $owner)->where('stream', $stream)->value('revision') ?? 0);
    }

    private function saveHead(int $owner, string $stream, int $revision): void
    {
        DB::table('religious_module_heads')->updateOrInsert(['religious_account_id' => $owner, 'stream' => $stream], ['revision' => $revision]);
    }

    public function personal(ReligiousAccount $account, array $operations): array
    {
        $seen = [];
        $validated = [];
        $this->require(array_is_list($operations) && count($operations) <= 250);
        foreach ($operations as $op) {
            $this->require(is_array($op));
            $this->fields($op, ['id', 'key', 'base_revision', 'deleted', 'value']);
            $id = $this->uuid($op['id']);
            $this->require(! isset($seen[$id]), 'Duplicate operation ID.');
            $seen[$id] = true;
            $key = $this->key($op['key']);
            $deleted = $this->boolean($op['deleted']);
            $this->require(! $deleted || $op['value'] === null);
            $validated[] = ['id' => $id, 'key' => $key, 'base_revision' => $this->number($op['base_revision'], 0, PHP_INT_MAX),
                'deleted' => $deleted, 'value' => $deleted ? null : $this->entity($key, $op['value'])];
        }

        return DB::transaction(function () use ($account, $validated): array {
            $owner = (int) ReligiousAccount::query()->lockForUpdate()->findOrFail($account->id)->id;
            $revision = $this->head($owner, 'personal');
            $acknowledged = $conflicts = [];
            foreach ($validated as $op) {
                $hash = hash('sha256', $this->json($op));
                $receipt = DB::table('religious_personal_operations')->where('religious_account_id', $owner)->where('operation_id', $op['id'])->first();
                if ($receipt) {
                    abort_unless(hash_equals($receipt->payload_hash, $hash), 409, 'Operation ID reused with different data.');
                    $conflict = (bool) $receipt->conflicted;
                } else {
                    $entity = DB::table('religious_personal_entities')->where('religious_account_id', $owner)->where('entity_key', $op['key'])->first();
                    $conflict = (int) ($entity->revision ?? 0) !== $op['base_revision'];
                    if (! $conflict) {
                        DB::table('religious_personal_entities')->updateOrInsert(['religious_account_id' => $owner, 'entity_key' => $op['key']],
                            ['value' => $op['deleted'] ? null : $this->json($op['value']), 'deleted' => $op['deleted'], 'revision' => ++$revision]);
                    }
                    DB::table('religious_personal_operations')->insert(['religious_account_id' => $owner, 'operation_id' => $op['id'],
                        'entity_key' => $op['key'], 'payload_hash' => $hash, 'conflicted' => $conflict]);
                }
                $acknowledged[] = $op['id'];
                if ($conflict) {
                    $conflicts[] = $op['key'];
                }
            }
            $this->saveHead($owner, 'personal', $revision);

            return ['revision' => $revision, 'acknowledged' => $acknowledged, 'conflicts' => array_values(array_unique($conflicts)),
                'entities' => (object) DB::table('religious_personal_entities')->where('religious_account_id', $owner)->orderBy('id')->get()
                    ->mapWithKeys(fn ($row) => [$row->entity_key => ['value' => $row->deleted ? null : json_decode($row->value, true, flags: JSON_THROW_ON_ERROR),
                        'deleted' => (bool) $row->deleted, 'revision' => (int) $row->revision]])->all()];
        });
    }

    public function event(array $event): array
    {
        $this->fields($event, ['id', 'device_id', 'session_id', 'subject_id', 'epoch', 'kind', 'amount', 'occurred_at', 'day', 'utc_offset_minutes', 'reverses']);
        $id = $this->uuid($event['id']);
        $kind = $event['kind'];
        $this->require(in_array($kind, ['count', 'undo', 'complete', 'reset', 'habit', 'quranPage', 'breathing'], true));
        $zero = in_array($kind, ['undo', 'complete', 'reset'], true);
        $amount = $this->number($event['amount'], $zero ? 0 : 1, $zero ? 0 : ($kind === 'quranPage' ? 604 : 1000000));
        $reverses = $event['reverses'] === null ? null : $this->uuid($event['reverses']);
        $this->require(($kind === 'undo') === ($reverses !== null) && $id !== $reverses);
        $at = $event['occurred_at'];
        $this->require(is_string($at) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z$/D', $at) === 1);
        $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i:s.v\Z', $at, new \DateTimeZone('UTC'));
        $this->require($date !== false && $date->format('Y-m-d\TH:i:s.v\Z') === $at);

        return ['id' => $id, 'device_id' => $this->uuid($event['device_id']), 'session_id' => $this->uuid($event['session_id']),
            'subject_id' => $this->subject($event['subject_id']), 'epoch' => $this->uuid($event['epoch']), 'kind' => $kind,
            'amount' => $amount, 'occurred_at' => $at, 'day' => $this->date($event['day']),
            'utc_offset_minutes' => $this->number($event['utc_offset_minutes'], -840, 840), 'reverses' => $reverses];
    }

    public function activity(ReligiousAccount $account, array $events, int $cursor): array
    {
        $this->require(array_is_list($events) && count($events) <= 250 && $cursor >= 0);
        $validated = array_map(fn ($event) => $this->event($event), $events);
        $this->require(count(array_unique(array_column($validated, 'id'))) === count($validated), 'Duplicate event ID.');

        return DB::transaction(function () use ($account, $validated, $cursor): array {
            $owner = (int) ReligiousAccount::query()->lockForUpdate()->findOrFail($account->id)->id;
            $revision = $this->head($owner, 'activity');
            abort_if($cursor > $revision, 409, 'Activity cursor is ahead of this account.');
            $acknowledged = [];
            foreach ($validated as $event) {
                $json = $this->json($event);
                $hash = hash('sha256', $json);
                $receipt = DB::table('religious_activity_events')->where('religious_account_id', $owner)->where('event_id', $event['id'])->first();
                if ($receipt) {
                    abort_unless(hash_equals($receipt->payload_hash, $hash), 409, 'Event ID reused with different data.');
                } else {
                    if ($event['kind'] === 'undo') {
                        $target = DB::table('religious_activity_events')->where('religious_account_id', $owner)->where('event_id', $event['reverses'])->first();
                        $this->require($target !== null && ! in_array($target->kind, ['undo', 'reset', 'complete'], true) &&
                            $target->session_id === $event['session_id'] && $target->subject_id === $event['subject_id'] && $target->epoch === $event['epoch'], 'Undo target not available in this session.');
                        abort_if(DB::table('religious_activity_events')->where('religious_account_id', $owner)->where('reverses', $event['reverses'])->exists(), 409, 'Activity already undone.');
                    }
                    DB::table('religious_activity_events')->insert(['religious_account_id' => $owner, 'event_id' => $event['id'],
                        'device_id' => $event['device_id'], 'session_id' => $event['session_id'], 'subject_id' => $event['subject_id'],
                        'epoch' => $event['epoch'], 'kind' => $event['kind'], 'amount' => $event['amount'],
                        'local_day' => $event['day'], 'utc_offset_minutes' => $event['utc_offset_minutes'],
                        'occurred_at' => (new DateTimeImmutable($event['occurred_at']))->format('Y-m-d H:i:s.v'),
                        'reverses' => $event['reverses'], 'payload' => $json, 'payload_hash' => $hash, 'revision' => ++$revision]);
                }
                $acknowledged[] = $event['id'];
            }
            $this->saveHead($owner, 'activity', $revision);
            $rows = DB::table('religious_activity_events')->where('religious_account_id', $owner)->where('revision', '>', $cursor)->orderBy('revision')->limit(500)->get();
            $next = $rows->isEmpty() ? $cursor : (int) $rows->last()->revision;

            return ['revision' => $revision, 'cursor' => $next, 'has_more' => $next < $revision, 'acknowledged' => $acknowledged,
                'events' => $rows->map(fn ($row) => ['revision' => (int) $row->revision, 'event' => json_decode($row->payload, true, flags: JSON_THROW_ON_ERROR)])->all()];
        });
    }
}
