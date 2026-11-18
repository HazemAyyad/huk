<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class AssociationForms
{
    public function __construct(private AssociationAccess $access, private AssociationPortal $portal, private AssociationAudience $audience,
        private AssociationOperations $operations, private AssociationMedia $media, private AssociationNotifications $notifications) {}

    private function decode(object $row): array
    {
        $data = (array) $row;
        foreach (['fields', 'audience', 'review_categories', 'answers', 'form_snapshot', 'editable_fields', 'submission_location'] as $key) {
            if (isset($data[$key])) { $data[$key] = json_decode($data[$key], true, 512, JSON_THROW_ON_ERROR); }
        }
        $data['_revision'] = $this->operations->revision($row);
        $data['_label'] = $data['title'] ?? (isset($data['beneficiary_id']) ? 'طلب رقم '.$row->id : 'نموذج رقم '.$row->id);
        if (isset($data['beneficiary_id'])) {
            $person = DB::table('telegram_users')->where('id', $data['beneficiary_id'])->first(['id', 'full_name', 'national_id']);
            $data['beneficiary'] = $person ? (array) $person : null;
            $data['_label'] = ($person?->full_name ?? 'طلب رقم '.$row->id).' · '.($data['form_snapshot']['title'] ?? 'طلب خدمة');
        }

        return $data;
    }

    private function submissions(object $grant)
    {
        $query = DB::table('association_form_submissions')->whereNotNull('submitted_at')->where('status', '!=', 'archived');
        if (! $this->access->global($grant)) {
            $query->whereIn('beneficiary_id', $this->access->beneficiaryQuery($grant)->select('telegram_users.id'));
            $ids = $this->access->categories($grant);
            $query->whereIn('form_id', DB::table('association_forms')->where(function ($query) use ($ids): void {
                foreach ($ids as $id) { $query->orWhereJsonContains('review_categories', $id); }
            })->select('id'));
        }

        return $query;
    }

    public function adminList(AppAccount $account, string $module, array $filter): array
    {
        $grant = $this->access->require($account, $module);
        $query = $this->adminQuery($grant, $module, $filter);
        $page = $query->orderByDesc('id')->paginate(30);

        return [...$this->metadata($grant, $module), 'items' => array_map(fn ($row) => $this->decode($row), $page->items()), 'page' => $page->currentPage(), 'has_more' => $page->hasMorePages(), 'total' => $page->total()];
    }

    public function adminQuery(object $grant, string $module, array $filter = [])
    {
        abort_unless(in_array($module, ['forms', 'submissions'], true), 404);
        $query = $module === 'forms' ? DB::table('association_forms')->whereNull('deleted_at') : $this->submissions($grant);
        if ($module === 'forms' && ! $this->access->global($grant)) { $query->where('created_by', $grant->staff_id); }
        if (! empty($filter['q']) && $module === 'forms') { $query->where('title', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $filter['q']).'%'); }
        if (! empty($filter['q']) && $module === 'submissions') {
            $search = '%'.str_replace(['%', '_'], ['\%', '\_'], $filter['q']).'%';
            $query->whereIn('beneficiary_id', $this->access->beneficiaryQuery($grant)->where(fn ($q) => $q->where('full_name', 'like', $search)->orWhere('national_id', 'like', $search))->select('telegram_users.id'));
        }
        if (! empty($filter['status'])) { $query->where('status', $filter['status']); }
        if (isset($filter['form_id']) && $module === 'submissions') { $query->where('form_id', (int) $filter['form_id']); }
        return $query;
    }

    private function metadata(object $grant, string $module): array
    {
        return ['module' => $module, 'title' => AssociationModules::DEFINITIONS[$module]['title'], 'fields' => [],
            'actions' => array_values(array_filter(AssociationModules::definition($module)['actions'], fn ($action) => $this->access->allows($grant, $module, $action))),
            'can_create' => $this->access->allows($grant, $module, 'create'), 'can_edit' => $this->access->allows($grant, $module, 'update')];
    }

    public function adminShow(AppAccount $account, string $module, int $id): array
    {
        $grant = $this->access->require($account, $module);
        $row = ($module === 'forms' ? DB::table('association_forms')->whereNull('deleted_at') : $this->submissions($grant))->where('id', $id)->first() ?? abort(404);

        return [...$this->metadata($grant, $module), 'record' => $this->decode($row)];
    }

    public function saveForm(AppAccount $account, array $input, ?int $id = null): array
    {
        $grant = $this->access->require($account, 'forms', $id ? 'update' : 'create');
        return $this->operations->run($account, 'forms.'.($id ?? 'new').'.save', $input, function () use ($account, $grant, $input, $id): array {
            $old = $id ? DB::table('association_forms')->where('id', $id)->whereNull('deleted_at')->lockForUpdate()->first() ?? abort(404) : null;
            if ($old) { $this->operations->expect($old, $input); }
            $data = Validator::make($input['record'] ?? [], ['title' => 'required|string|max:255', 'title_en' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:10000', 'description_en' => 'nullable|string|max:10000', 'fields' => 'required|array|min:1|max:100',
                'fields.*.key' => 'required|string|distinct|regex:/^[a-z][a-z0-9_]{0,63}$/D', 'fields.*.label' => 'required|string|max:255',
                'fields.*.label_en' => 'nullable|string|max:255', 'fields.*.type' => 'required|in:text,textarea,number,date,select,multiselect,image,file,location',
                'fields.*.required' => 'required|boolean', 'fields.*.options' => 'sometimes|array|max:200', 'fields.*.options.*' => 'string|distinct|max:255',
                'fields.*.min' => 'sometimes|nullable|numeric', 'fields.*.max' => 'sometimes|nullable|numeric',
                'audience' => 'present|array', 'review_categories' => 'present|array|max:100', 'review_categories.*' => 'integer|distinct|exists:categories,id',
                'status' => 'required|in:draft,published,closed', 'opens_at' => 'nullable|date', 'closes_at' => 'nullable|date|after:opens_at',
                'capacity' => 'nullable|integer|min:1', 'requires_location' => 'required|boolean'])->validate();
            $data['audience'] = $this->audience->validate($data['audience'], $grant);
            foreach ($data['fields'] as &$field) {
                $field = array_intersect_key($field, array_flip(['key', 'label', 'label_en', 'type', 'required', 'options', 'min', 'max']));
                if (in_array($field['type'], ['select', 'multiselect'], true)) { abort_unless(count($field['options'] ?? []) > 0, 422, 'field_options_required'); }
                abort_if(isset($field['min'], $field['max']) && $field['min'] > $field['max'], 422, 'invalid_field_range');
            }
            unset($field);
            if (isset($data['capacity'])) { abort_if($old && $data['capacity'] < DB::table('association_form_submissions')->where('form_id', $id)->whereNotNull('submitted_at')->count(), 422, 'capacity_below_submitted_count'); }
            foreach (['fields', 'audience', 'review_categories'] as $key) { $data[$key] = json_encode($data[$key], JSON_THROW_ON_ERROR); }
            foreach (['opens_at', 'closes_at'] as $key) { $data[$key] = empty($data[$key]) ? null : CarbonImmutable::parse($data[$key])->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'); }
            $data['revision'] = ($old->revision ?? 0) + 1;
            $data['updated_by'] = $grant->staff_id;
            $data['updated_at'] = now();
            if ($id) { DB::table('association_forms')->where('id', $id)->update($data); }
            else { $id = DB::table('association_forms')->insertGetId([...$data, 'created_by' => $grant->staff_id, 'created_at' => now()]); }
            $row = DB::table('association_forms')->where('id', $id)->first();
            $this->operations->audit($account, $grant, 'forms', $id, $old ? 'update' : 'create', $old, $row);

            return ['record' => $this->decode($row)];
        });
    }

    private function available(object $form, object $person): bool
    {
        return $form->deleted_at === null && $form->status === 'published' && $this->audience->query(json_decode($form->audience ?? '[]', true))->where('telegram_users.id', $person->id)->exists();
    }

    private function open(object $form): bool
    {
        return (! $form->opens_at || CarbonImmutable::parse($form->opens_at)->lessThanOrEqualTo(now())) && (! $form->closes_at || CarbonImmutable::parse($form->closes_at)->isFuture());
    }

    private function portalView(object $form, ?object $submission = null): array
    {
        $data = $this->decode($form);
        unset($data['audience'], $data['review_categories'], $data['created_by'], $data['updated_by']);
        if ($submission?->submitted_at && $submission->form_snapshot) {
            $snapshot = json_decode($submission->form_snapshot, true);
            $data['fields'] = $snapshot['fields'];
        }
        $data['accepting'] = $form->status === 'published' && $this->open($form);
        $data['submission'] = $submission ? $this->decode($submission) : null;

        return $data;
    }

    public function portalList(AppAccount $account): array
    {
        $person = $this->portal->person($account);
        $items = [];
        foreach (DB::table('association_forms')->whereNull('deleted_at')->orderByDesc('id')->cursor() as $form) {
            $submission = DB::table('association_form_submissions')->where('form_id', $form->id)->where('beneficiary_id', $person->id)->first();
            if ($this->available($form, $person) || $submission) { $items[] = $this->portalView($form, $submission); }
        }

        return ['items' => $items];
    }

    public function portalShow(AppAccount $account, int $id): array
    {
        $person = $this->portal->person($account);
        $form = DB::table('association_forms')->where('id', $id)->first() ?? abort(404);
        $submission = DB::table('association_form_submissions')->where('form_id', $id)->where('beneficiary_id', $person->id)->first();
        abort_unless($this->available($form, $person) || $submission, 404);

        return $this->portalView($form, $submission);
    }

    private function answers(AppAccount $account, array $fields, array $answers, bool $final, ?object $old = null): array
    {
        $keys = array_column($fields, 'key');
        abort_if(array_diff(array_keys($answers), $keys), 422, 'unknown_form_answer');
        $rules = [];
        foreach ($fields as $field) {
            $key = $field['key'];
            $rule = [$final && $field['required'] ? 'required' : 'nullable'];
            $rule[] = match ($field['type']) { 'number' => 'numeric', 'date' => 'date', 'multiselect', 'location' => 'array', default => 'string' };
            if (in_array($field['type'], ['text', 'textarea'], true)) { $rule[] = 'max:30000'; }
            if ($field['type'] === 'select') { $rule[] = Rule::in($field['options']); }
            if ($field['type'] === 'multiselect') { $rules[$key.'.*'] = ['string', 'distinct', Rule::in($field['options'])]; }
            if (isset($field['min'])) { $rule[] = 'min:'.$field['min']; }
            if (isset($field['max'])) { $rule[] = 'max:'.$field['max']; }
            $rules[$key] = $rule;
            if ($field['type'] === 'location') { $rules += [$key.'.latitude' => 'required_with:'.$key.'|numeric|between:-90,90', $key.'.longitude' => 'required_with:'.$key.'|numeric|between:-180,180']; }
        }
        $data = Validator::make($answers, $rules)->validate();
        $oldAnswers = $old ? json_decode($old->answers, true) : [];
        foreach ($fields as $field) {
            $key = $field['key'];
            if (isset($data[$key]) && in_array($field['type'], ['image', 'file'], true)) {
                $data[$key] = $this->media->value($account, 'submissions', $key, $data[$key], $oldAnswers[$key] ?? null, $old ? (string) $old->id : null);
                if ($field['type'] === 'image') {
                    $mime = DB::table('association_uploads')->where('id', $this->media->id($data[$key]))->value('mime');
                    abort_unless(in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true), 422, 'image_attachment_required');
                }
            }
        }

        return $data;
    }

    public function submit(AppAccount $account, int $id, array $input): array
    {
        $person = $this->portal->person($account);
        Validator::make($input, ['answers' => 'present|array|max:100', 'final' => 'required|boolean', 'form_revision' => 'required|integer|min:1'])->validate();
        return $this->operations->run($account, 'forms.'.$id.'.submit', $input, function () use ($account, $person, $id, $input): array {
            $form = DB::table('association_forms')->where('id', $id)->lockForUpdate()->first() ?? abort(404);
            $old = DB::table('association_form_submissions')->where('form_id', $id)->where('beneficiary_id', $person->id)->lockForUpdate()->first();
            if ($old) { $this->operations->expect($old, $input); }
            $correction = $old?->status === 'correction_requested';
            abort_unless($correction || ($this->available($form, $person) && $this->open($form)), 409, 'form_not_accepting');
            abort_if($old?->submitted_at && ! $correction, 409, 'form_already_submitted');
            if (! $correction) { abort_unless((int) $form->revision === $input['form_revision'], 409, 'form_schema_changed'); }
            $fields = $correction && $old->form_snapshot ? json_decode($old->form_snapshot, true)['fields'] : json_decode($form->fields, true);
            $answers = $input['answers'];
            if ($correction) {
                $editable = json_decode($old->editable_fields ?? '[]', true);
                abort_if(array_diff(array_keys($answers), $editable), 422, 'form_field_not_open_for_correction');
                $answers = [...json_decode($old->answers, true), ...$answers];
            }
            $answers = $this->answers($account, $fields, $answers, (bool) $input['final'], $old);
            $location = $input['location'] ?? null;
            if ($input['final'] && $form->requires_location && ! $correction) {
                $location = Validator::make(['location' => $location], ['location' => 'required|array', 'location.latitude' => 'required|numeric|between:-90,90',
                    'location.longitude' => 'required|numeric|between:-180,180', 'location.accuracy' => 'required|numeric|min:0',
                    'location.captured_at' => 'required|date|after:'.now()->subMinutes(10)->toIso8601String().'|before:'.now()->addMinute()->toIso8601String()])->validate()['location'];
            }
            if ($input['final'] && ! $old?->submitted_at && $form->capacity) {
                abort_if(DB::table('association_form_submissions')->where('form_id', $id)->whereNotNull('submitted_at')->count() >= $form->capacity, 409, 'form_capacity_reached');
            }
            $values = ['answers' => json_encode($answers, JSON_THROW_ON_ERROR), 'status' => $input['final'] ? 'submitted' : ($correction ? 'correction_requested' : 'draft'),
                'revision' => ($old->revision ?? 0) + 1, 'updated_at' => now()];
            if (! $old) { $values += ['account_id' => $account->id, 'created_at' => now()]; }
            if ($input['final']) {
                $values['submitted_at'] = $old->submitted_at ?? now();
                $values['editable_fields'] = '[]';
                if (! $old?->submitted_at) {
                    $values['form_snapshot'] = json_encode(['title' => $form->title, 'revision' => $form->revision, 'fields' => $fields], JSON_THROW_ON_ERROR);
                    $values['submission_location'] = $location ? json_encode($location, JSON_THROW_ON_ERROR) : null;
                }
            }
            DB::table('association_form_submissions')->updateOrInsert(['form_id' => $id, 'beneficiary_id' => $person->id], $values);
            $row = DB::table('association_form_submissions')->where('form_id', $id)->where('beneficiary_id', $person->id)->first();
            $this->media->bind($account, 'submissions', (string) $row->id, $answers);
            $this->operations->audit($account, (object) ['staff_id' => null], 'submissions', $row->id, $input['final'] ? 'submit' : 'draft', $old, $row);

            return ['submission' => $this->decode($row)];
        }, function (array $result) use ($person): void {
            abort_unless(DB::table('association_form_submissions')->where('id', $result['submission']['id'])->where('beneficiary_id', $person->id)->exists(), 404);
        });
    }

    public function review(AppAccount $account, int $id, array $input): array
    {
        $grant = $this->access->require($account, 'submissions', 'review');
        $this->submissions($grant)->where('id', $id)->first() ?? abort(404);
        return $this->operations->run($account, 'submissions.'.$id.'.review', $input, function () use ($account, $grant, $id, $input): array {
            $old = $this->submissions($grant)->where('id', $id)->lockForUpdate()->first() ?? abort(404);
            $this->operations->expect($old, $input);
            abort_unless($old->submitted_at, 409, 'draft_not_submitted');
            $data = Validator::make($input, ['status' => 'required|in:approved,rejected,correction_requested', 'reason' => 'nullable|string|max:5000',
                'editable_fields' => 'sometimes|array|max:100', 'editable_fields.*' => 'string|distinct'])->validate();
            $editable = $data['editable_fields'] ?? [];
            if ($data['status'] === 'correction_requested') {
                Validator::make($data, ['reason' => 'required|string|min:3', 'editable_fields' => 'required|array|min:1'])->validate();
                $fields = json_decode($old->form_snapshot, true)['fields'];
                abort_if(array_diff($editable, array_column($fields, 'key')), 422, 'unknown_correction_field');
            } else { $editable = []; }
            if ($data['status'] === 'rejected') { Validator::make($data, ['reason' => 'required|string|min:3'])->validate(); }
            DB::table('association_form_submissions')->where('id', $id)->update(['status' => $data['status'], 'reason' => $data['reason'] ?? null,
                'editable_fields' => json_encode($editable), 'revision' => $old->revision + 1, 'reviewed_by' => $grant->staff_id, 'reviewed_at' => now(), 'updated_at' => now()]);
            $row = DB::table('association_form_submissions')->where('id', $id)->first();
            $this->notifications->beneficiary($old->beneficiary_id, $grant->staff_id, 'تحديث طلب خدمة', ($data['reason'] ?? null) ?: 'تم تحديث حالة طلبك.', ['type' => 'submission', 'id' => $id, 'form_id' => $old->form_id]);
            $this->operations->audit($account, $grant, 'submissions', $id, 'review', $old, $row, $data['reason'] ?? null);

            return ['record' => $this->decode($row)];
        }, fn () => $this->submissions($grant)->where('id', $id)->first() ?? abort(404));
    }

    public function editSubmission(AppAccount $account, int $id, array $input): array
    {
        $grant = $this->access->require($account, 'submissions', 'update');
        $this->submissions($grant)->where('id', $id)->first() ?? abort(404);
        return $this->operations->run($account, 'submissions.'.$id.'.edit', $input, function () use ($account, $grant, $id, $input): array {
            $old = $this->submissions($grant)->where('id', $id)->lockForUpdate()->first() ?? abort(404);
            $this->operations->expect($old, $input);
            $input = Validator::make($input, ['answers' => 'required|array|max:100', 'reason' => 'required|string|min:3|max:5000'])->validate();
            $fields = json_decode($old->form_snapshot, true)['fields'];
            $answers = $this->answers($account, $fields, [...json_decode($old->answers, true), ...$input['answers']], true, $old);
            DB::table('association_form_submissions')->where('id', $id)->update(['answers' => json_encode($answers, JSON_THROW_ON_ERROR), 'revision' => $old->revision + 1, 'reviewed_by' => $grant->staff_id, 'updated_at' => now()]);
            $this->media->bind($account, 'submissions', (string) $id, $answers);
            $row = DB::table('association_form_submissions')->where('id', $id)->first();
            $this->operations->audit($account, $grant, 'submissions', $id, 'staff_edit', $old, $row, $input['reason']);

            return ['record' => $this->decode($row)];
        }, fn () => $this->submissions($grant)->where('id', $id)->first() ?? abort(404));
    }

    public function delete(AppAccount $account, string $module, int $id, array $input): array
    {
        $grant = $this->access->require($account, $module, 'delete');
        return $this->operations->run($account, $module.'.'.$id.'.delete', $input, function () use ($account, $grant, $module, $id, $input): array {
            $query = $module === 'forms' ? DB::table('association_forms')->whereNull('deleted_at') : $this->submissions($grant);
            $old = $query->where('id', $id)->lockForUpdate()->first() ?? abort(404);
            $this->operations->expect($old, $input);
            Validator::make($input, ['reason' => 'required|string|min:3|max:5000'])->validate();
            DB::table($module === 'forms' ? 'association_forms' : 'association_form_submissions')->where('id', $id)->update(
                $module === 'forms' ? ['deleted_at' => now(), 'updated_at' => now(), 'revision' => $old->revision + 1] : ['status' => 'archived', 'reason' => $input['reason'], 'updated_at' => now(), 'revision' => $old->revision + 1]);
            $this->operations->audit($account, $grant, $module, $id, 'delete', $old, null, $input['reason']);

            return ['id' => $id, 'deleted' => true];
        });
    }

    public function attachmentField(AppAccount $account, int $formId, string $key): array
    {
        $form = $this->portalShow($account, $formId);
        $submission = $form['submission'];
        $correction = ($submission['status'] ?? null) === 'correction_requested';
        abort_unless($correction || ($form['accepting'] && empty($submission['submitted_at'])), 409, 'form_not_accepting');
        if ($correction) { abort_unless(in_array($key, $submission['editable_fields'] ?? [], true), 403, 'form_field_not_open_for_correction'); }
        $field = collect($form['fields'])->firstWhere('key', $key);
        abort_unless($field && in_array($field['type'], ['image', 'file'], true), 422, 'invalid_attachment_field');

        return $field;
    }

    public function canReadSubmission(AppAccount $account, int $id): bool
    {
        $row = DB::table('association_form_submissions')->where('id', $id)->first();
        if (! $row) { return false; }
        try {
            if ($this->portal->person($account)->id === (int) $row->beneficiary_id) { return true; }
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException|\Illuminate\Database\Eloquent\ModelNotFoundException) {}
        if (! $account->currentAccessToken()?->can('app:admin') || ! $this->access->can($account, 'submissions')) { return false; }

        return $this->submissions($this->access->require($account, 'submissions'))->where('id', $id)->exists();
    }
}
