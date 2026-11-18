<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class AssociationOperations
{
    public function run(AppAccount $account, string $command, array $input, callable $apply, ?callable $authorizeReplay = null): array
    {
        Validator::make($input, ['operation_id' => 'required|uuid'])->validate();
        $hash = hash('sha256', $this->canonical(['command' => $command, 'input' => $input]));
        try {
            return DB::transaction(function () use ($account, $command, $input, $hash, $apply, $authorizeReplay): array {
                $existing = DB::table('association_operations')->where('account_id', $account->id)->where('operation_id', $input['operation_id'])->lockForUpdate()->first();
                if ($existing) {
                    return $this->result($existing, $hash, $authorizeReplay);
                }
                $id = DB::table('association_operations')->insertGetId([
                    'account_id' => $account->id, 'operation_id' => $input['operation_id'],
                    'command' => $command, 'request_hash' => $hash, 'created_at' => now(), 'updated_at' => now(),
                ]);
                $result = $apply();
                DB::table('association_operations')->where('id', $id)->update(['result' => json_encode($result, JSON_THROW_ON_ERROR), 'updated_at' => now()]);

                return $result;
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            $existing = DB::table('association_operations')->where('account_id', $account->id)->where('operation_id', $input['operation_id'])->first();
            if (! $existing) {
                throw $exception;
            }

            return $this->result($existing, $hash, $authorizeReplay);
        }
    }

    private function result(object $operation, string $hash, ?callable $authorizeReplay): array
    {
        abort_unless(hash_equals($operation->request_hash, $hash) && $operation->result !== null, 409, 'operation_id_conflict');

        $result = json_decode($operation->result, true, 512, JSON_THROW_ON_ERROR);
        if ($authorizeReplay) {
            $authorizeReplay($result);
        }

        return $result;
    }

    public function revision(array|object $record): string
    {
        $data = (array) $record;
        unset($data['_revision']);

        return hash('sha256', $this->canonical($data));
    }

    public function expect(array|object $record, array $input): void
    {
        Validator::make($input, ['expected_revision' => 'required|string|size:64'])->validate();
        abort_unless(hash_equals($this->revision($record), $input['expected_revision']), 409, 'association_record_changed');
    }

    private function canonical(mixed $value): string
    {
        $sort = function ($value) use (&$sort) {
            if (! is_array($value)) {
                return $value;
            }
            if (! array_is_list($value)) {
                ksort($value);
            }

            return array_map($sort, $value);
        };

        return json_encode($sort($value), JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR);
    }

    public function audit(AppAccount $account, object $grant, string $module, string|int|null $id, string $action, mixed $before = null, mixed $after = null, ?string $reason = null): void
    {
        DB::table('association_audit')->insert([
            'account_id' => $account->id, 'staff_id' => $grant->staff_id, 'module' => $module,
            'record_id' => $id, 'action' => $action, 'before' => $before === null ? null : json_encode($this->redact($before), JSON_THROW_ON_ERROR),
            'after' => $after === null ? null : json_encode($this->redact($after), JSON_THROW_ON_ERROR), 'reason' => $reason, 'created_at' => now(),
        ]);
    }

    private function redact(mixed $data): mixed
    {
        if (is_object($data)) {
            $data = (array) $data;
        }
        if (! is_array($data)) {
            return $data;
        }
        foreach ($data as $key => $value) {
            if (preg_match('/password|token|api_secret|api_key|private_key|credential/i', (string) $key)) {
                unset($data[$key]);
            } else {
                $data[$key] = $this->redact($value);
            }
        }

        return $data;
    }
}
