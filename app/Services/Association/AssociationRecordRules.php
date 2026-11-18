<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class AssociationRecordRules
{
    public const LOOKUPS = ['telegram_user_id' => 'beneficiaries', 'category_id' => 'categories', 'event_id' => 'events',
        'family_member_id' => 'family-members', 'case_profile_id' => 'case-types', 'governorate_id' => 'governorates',
        'city_id' => 'cities', 'area_id' => 'areas', 'guardian_id' => 'cases-guardians', 'user_id' => 'staff',
        'family_id' => 'cases-breadwinners', 'stranded_family_id' => 'cases-stranded', 'solidarity_housing_id' => 'cases-housing',
        'injured_case_id' => 'cases-injured', 'patient_case_id' => 'cases-patients', 'amputation_id' => 'cases-amputations', 'beneficiary_id' => 'ramadan-beneficiaries'];

    public function __construct(private AssociationRecords $records, private AssociationAccess $access, private AssociationMedia $media) {}

    public function validate(AppAccount $account, object $grant, string $module, array $record, ?Model $old = null): array
    {
        $fields = $this->records->fields($module);
        $allowed = [];
        $rules = [];
        foreach ($fields as $field) {
            if (! empty($field['readonly'])) {
                continue;
            }
            $key = $field['key'];
            $allowed[] = $key;
            $required = $old === null && ! empty($field['required']);
            $rule = [$required ? 'required' : 'sometimes'];
            if ($field['nullable'] ?? ! $required) {
                $rule[] = 'nullable';
            }
            $rule[] = match ($field['type']) {
                'bool' => 'boolean', 'number' => 'numeric', 'lookup' => 'integer', 'json', 'lookup_many' => 'array',
                'date', 'datetime' => 'date', 'file' => is_array($record[$key] ?? null) ? 'array' : 'string',
                'time' => 'regex:/^([01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/D',
                default => 'string',
            };
            if ($field['type'] === 'select') {
                $rule[] = Rule::in(array_column($field['options'], 'value'));
            } elseif (in_array($field['type'], ['text', 'select', 'file'], true) && ! is_array($record[$key] ?? null)) {
                $rule[] = 'max:'.($field['type'] === 'file' ? 2048 : 255);
            } elseif ($field['type'] === 'textarea') {
                $rule[] = 'max:30000';
            } elseif (in_array($field['type'], ['json', 'lookup_many'], true)) {
                $rule[] = 'max:200';
            }
            if (str_ends_with($key, 'birth_date') || $key === 'birth_date') {
                $rule[] = 'before_or_equal:today';
            }
            if ($field['type'] === 'number' && in_array($key, ['age', 'amount', 'basic_salary', 'daily_meals', 'family_members_count', 'value'], true)) {
                $rule[] = 'min:0';
            }
            if ($field['type'] === 'lookup') {
                $rule[] = 'integer';
            }
            $rules[$key] = $rule;
        }
        if ($module === 'beneficiaries') {
            $rules['categories'] = ['sometimes', 'array', 'max:100'];
            $rules['categories.*'] = ['integer', 'distinct'];
            $rules['primary_category_id'] = ['sometimes', 'nullable', 'integer'];
            $rules['change_main_category'] = ['sometimes', 'boolean'];
            $allowed = [...$allowed, 'primary_category_id'];
        }
        if ($module === 'events') {
            $rules = [...$rules, 'ends_at' => 'sometimes|nullable|date', 'reply_deadline' => 'sometimes|nullable|date',
                'capacity' => 'sometimes|nullable|integer|min:1|max:1000000', 'reminder_minutes' => 'sometimes|nullable|integer|min:1|max:525600',
                'audience' => 'sometimes|nullable|array'];
            $allowed = [...$allowed, 'ends_at', 'reply_deadline', 'capacity', 'reminder_minutes', 'audience'];
        }
        if ($module === 'payments') {
            $rules['amount'] = ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'];
        }
        $unknown = array_diff(array_keys($record), $allowed);
        if ($unknown) {
            throw ValidationException::withMessages(array_fill_keys($unknown, 'هذا الحقل غير قابل للتعديل من هذا الإجراء.'));
        }
        $validated = Validator::make($record, $rules)->validate();
        if (isset($validated['national_id'])) {
            $validated['national_id'] = app(AssociationIdentities::class)->normalize((string) $validated['national_id']);
        }
        if ($module === 'ramadan-beneficiaries' && isset($validated['national_id'])
            && DB::table('ramadan_beneficiaries')->where('national_id', $validated['national_id'])->when($old, fn ($q) => $q->where('id', '!=', $old->id))->exists()) {
            throw ValidationException::withMessages(['national_id' => 'الهوية مسجلة مسبقًا في مستفيدي رمضان، بما في ذلك السجلات المحفوظة بعد الحذف.']);
        }
        foreach (self::LOOKUPS as $key => $lookup) {
            if ((collect($fields)->firstWhere('key', $key)['type'] ?? null) !== 'lookup') { continue; }
            if (! isset($validated[$key])) {
                continue;
            }
            $query = $this->records->query($lookup, $grant);
            if (! $query->whereKey((int) $validated[$key])->exists()) {
                throw ValidationException::withMessages([$key => 'السجل المرتبط غير موجود ضمن نطاقك.']);
            }
        }
        if ($module === 'beneficiaries' && isset($validated['categories'])) {
            $ids = array_values(array_unique(array_map('intval', $validated['categories'])));
            if (! $this->access->global($grant) && array_diff($ids, $this->access->categories($grant))) {
                throw ValidationException::withMessages(['categories' => 'لا يمكن ربط فئة خارج نطاقك.']);
            }
            if (DB::table('categories')->whereIn('id', $ids)->count() !== count($ids)) {
                throw ValidationException::withMessages(['categories' => 'إحدى الفئات غير موجودة.']);
            }
            $validated['categories'] = $ids;
        }
        $model = $old ?? $this->records->model($module);
        $columns = array_column($this->records->columns($module), null, 'name');
        foreach ($fields as $field) {
            $key = $field['key'];
            if (! array_key_exists($key, $validated)) {
                continue;
            }
            if ($field['type'] === 'file') {
                $validated[$key] = $this->media->value($account, $module, $key, $validated[$key], $old?->getAttribute($key), $old ? (string) $old->getKey() : null);
            }
            if ($validated[$key] !== null && in_array($field['type'], ['date', 'datetime'], true)) {
                $date = CarbonImmutable::parse($validated[$key], config('app.timezone'))->setTimezone(config('app.timezone'));
                $validated[$key] = $field['type'] === 'date' ? $date->toDateString() : $date->format('Y-m-d H:i:s');
            }
            if (is_array($validated[$key]) && isset($columns[$key]) && ! in_array($model->getCasts()[$key] ?? '', ['array', 'json', 'object', 'collection'], true)) {
                $validated[$key] = json_encode($validated[$key], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            }
        }

        return $validated;
    }
}
