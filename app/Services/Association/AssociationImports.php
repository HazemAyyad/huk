<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class AssociationImports
{
    public function __construct(private AssociationAccess $access, private AssociationRecords $records, private AssociationRecordRules $rules,
        private AssociationEntities $entities, private AssociationOperations $operations, private AssociationIdentities $identities, private AssociationSheetRows $sheets) {}

    private function headers(array $row, string $module): array
    {
        $aliases = ['الهوية' => 'national_id', 'رقم الهوية' => 'national_id', 'الاسم' => 'full_name', 'الاسم الكامل' => 'full_name', 'الفئات' => 'categories'];
        foreach ($this->records->fields($module) as $field) {
            if (! empty($field['readonly']) || in_array($field['type'], ['file', 'password'], true)) { continue; }
            foreach ([$field['key'], $field['label'], $field['label_en']] as $alias) { $aliases[mb_strtolower(trim($alias))] = $field['key']; }
        }
        $columns = [];
        foreach ($row as $value) {
            $value = trim(ltrim((string) $value, "\xEF\xBB\xBF"));
            $key = $aliases[mb_strtolower($value)] ?? $aliases[mb_strtolower(str_replace(' ', '_', $value))] ?? null;
            if (! $key || in_array($key, $columns, true)) { throw ValidationException::withMessages(['headers' => 'عمود غير معروف أو مكرر: '.$value]); }
            $columns[] = $key;
        }

        return $columns;
    }

    public function preview(AppAccount $account, string $module, array $input): array
    {
        $grant = $this->access->require($account, $module, 'import');
        $data = Validator::make($input, ['upload_id' => 'required|uuid', 'mode' => 'required|in:create,upsert', 'category_ids' => 'sometimes|array|max:100', 'category_ids.*' => 'integer|distinct|min:1'])->validate();
        abort_unless(in_array($module, ['beneficiaries', 'embassy', 'ramadan-beneficiaries'], true), 404);
        $upload = DB::table('association_uploads')->where('id', $data['upload_id'])->where('account_id', $account->id)->where('module', $module)->where('field', '_import')->first() ?? abort(404);
        abort_unless(Storage::disk('local')->exists($upload->path), 404);
        $sheet = $this->sheets->read(Storage::disk('local')->path($upload->path), $upload->name, config('association.import_row_limit'));
        abort_unless(count($sheet) > 1, 422, 'import_sheet_is_empty');
        $headers = $this->headers(array_shift($sheet), $module);
        $fields = array_column($this->records->fields($module), null, 'key');
        $rows = []; $errors = []; $seen = [];
        foreach ($sheet as $index => $cells) {
            if (! array_filter($cells, fn ($value) => trim((string) $value) !== '')) { continue; }
            try {
                abort_unless(count($cells) <= count($headers), 422, 'too_many_cells_in_row');
                $record = [];
                foreach ($headers as $column => $key) {
                    $value = isset($cells[$column]) ? trim((string) $cells[$column]) : null;
                    if ($value === '') { continue; }
                    if ($value !== null && str_starts_with($value, '=')) { throw ValidationException::withMessages([$key => 'الصيغ الحسابية غير مدعومة في الاستيراد.']); }
                    if ($value === null) { continue; }
                    if (($fields[$key]['type'] ?? '') === 'bool') { $value = in_array(mb_strtolower($value), ['1', 'true', 'yes', 'نعم'], true) ? true : (in_array(mb_strtolower($value), ['0', 'false', 'no', 'لا'], true) ? false : $value); }
                    if (($fields[$key]['type'] ?? '') === 'lookup_many') { $value = array_map('intval', preg_split('/[,،;|]/u', $value)); }
                    $record[$key] = $value;
                }
                if ($module === 'beneficiaries' && ! isset($record['categories']) && ! empty($data['category_ids'])) { $record['categories'] = $data['category_ids']; }
                if (isset($record['national_id'])) {
                    $record['national_id'] = $this->identities->normalize($record['national_id']);
                    if (isset($seen[$record['national_id']])) { throw ValidationException::withMessages(['national_id' => 'الهوية مكررة في الكشف نفسه.']); }
                    $seen[$record['national_id']] = true;
                }
                $old = null;
                if ($data['mode'] === 'upsert') {
                    abort_unless(! empty($record['national_id']), 422, 'upsert_requires_national_id');
                    $matches = $this->records->query($module, $grant)->where('national_id', $record['national_id'])->limit(2)->get();
                    abort_if($matches->count() > 1, 422, 'identity_matches_multiple_records');
                    $old = $matches->first();
                }
                abort_unless($this->access->allows($grant, $module, $old ? 'update' : 'create'), 403);
                $validated = $this->rules->validate($account, $grant, $module, $record, $old);
                if ($module === 'beneficiaries') {
                    if (! $this->access->global($grant) && ! $old && empty($validated['categories'])) { throw ValidationException::withMessages(['categories' => 'اختر فئة مفوضة للمستفيد الجديد.']); }
                    if (isset($validated['national_id'])) { $this->identities->available($validated['national_id'], $old?->id); }
                }
                $rows[] = ['row' => $index + 2, 'record' => $record, 'id' => $old?->id, 'revision' => $old ? $this->operations->revision($this->records->revisionData($module, $old)) : null];
            } catch (ValidationException $exception) { $errors[] = ['row' => $index + 2, 'errors' => $exception->errors()]; }
            catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) { $errors[] = ['row' => $index + 2, 'errors' => ['row' => [$exception->getMessage() ?: 'الصف غير صالح ضمن الصلاحيات الحالية.']]]; }
        }
        $id = (string) Str::uuid();
        $payload = ['rows' => $rows, 'errors' => $errors, 'mode' => $data['mode']];
        $encoded = json_encode($payload, JSON_THROW_ON_ERROR);
        DB::table('association_import_previews')->insert(['id' => $id, 'account_id' => $account->id, 'module' => $module, 'payload' => $encoded,
            'sha256' => hash('sha256', $encoded), 'expires_at' => now()->addHour(), 'created_at' => now(), 'updated_at' => now()]);

        return ['preview_id' => $id, 'can_apply' => $rows !== [] && $errors === [], 'total_rows' => count($rows) + count($errors), 'valid_rows' => count($rows),
            'creates' => count(array_filter($rows, fn ($row) => $row['id'] === null)), 'updates' => count(array_filter($rows, fn ($row) => $row['id'] !== null)), 'errors' => $errors, 'sample' => array_slice($rows, 0, 30)];
    }

    public function apply(AppAccount $account, string $module, array $input): array
    {
        $grant = $this->access->require($account, $module, 'import');
        Validator::make($input, ['preview_id' => 'required|uuid'])->validate();
        return $this->operations->run($account, $module.'.import', $input, function () use ($account, $grant, $module, $input): array {
            $preview = DB::table('association_import_previews')->where('id', $input['preview_id'])->where('account_id', $account->id)->where('module', $module)->lockForUpdate()->first() ?? abort(404);
            abort_if($preview->applied_at || \Carbon\CarbonImmutable::parse($preview->expires_at)->isPast(), 409, 'import_preview_expired');
            abort_unless(hash_equals($preview->sha256, hash('sha256', $preview->payload)), 409, 'import_preview_corrupt');
            $payload = json_decode($preview->payload, true);
            abort_unless($payload['rows'] && ! $payload['errors'], 422, 'import_has_invalid_rows');
            $ids = [];
            foreach ($payload['rows'] as $row) {
                // Each normal command revalidates current permissions, identity,
                // scope, codes and optimistic revision inside this transaction.
                $result = $this->entities->save($account, $module, ['operation_id' => (string) Str::uuid(), 'record' => $row['record'], ...$row['revision'] ? ['expected_revision' => $row['revision']] : []], $row['id']);
                $ids[] = $result['record']['id'];
            }
            DB::table('association_import_previews')->where('id', $preview->id)->update(['applied_at' => now(), 'updated_at' => now()]);
            $this->operations->audit($account, $grant, $module, null, 'import', null, ['rows' => count($ids), 'ids' => $ids]);

            return ['imported' => count($ids), 'ids' => $ids];
        }, function (array $result) use ($grant, $module): void {
            abort_unless($this->records->query($module, $grant)->whereKey($result['ids'])->count() === count(array_unique($result['ids'])), 403, 'import_records_outside_current_scope');
        });
    }
}
