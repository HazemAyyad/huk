<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use App\Models\Category;
use App\Models\FamilyMember;
use App\Models\TelegramUser;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class AssociationEntities
{
    public function __construct(private AssociationAccess $access, private AssociationRecords $records,
        private AssociationOperations $operations, private AssociationRecordRules $rules,
        private AssociationIdentities $identities, private AssociationMedia $media) {}

    public function describe(string $module, Model $record): array
    {
        $data = $this->records->serialize($record, $module);
        $data['_deleted'] = $record->getAttribute('deleted_at') !== null || $record->getAttribute('deleted_by') !== null;
        if ($module === 'staff') { $data = [...$data, ...app(AssociationStaff::class)->details($record)]; }
        $data['_attachments'] = [];
        $data['_relations'] = [];
        foreach ([...AssociationRecords::PARENTS, 'family-members' => ['telegram_user_id', 'beneficiaries'], 'beneficiary-notes' => ['telegram_user_id', 'beneficiaries'],
            'orphan-requests' => ['telegram_user_id', 'beneficiaries'], 'amputation-requests' => ['telegram_user_id', 'beneficiaries'],
            'event-tasks' => ['event_id', 'events'], 'invitations' => ['event_id', 'events']] as $child => [$foreign, $parent]) {
            if ($parent === $module) { $data['_relations'][] = ['module' => $child, 'title' => AssociationModules::DEFINITIONS[$child]['title'], 'filter' => [$foreign => $record->getKey()]]; }
        }
        foreach ($this->records->fields($module) as $field) {
            if ($field['type'] !== 'file') { continue; }
            $value = $record->getAttribute($field['key']);
            if (is_string($value) && str_starts_with($value, '[')) { $value = json_decode($value, true); }
            $files = is_array($value) ? $value : [$value];
            foreach ($files as $index => $file) {
                if (! is_string($file) || $file === '') { continue; }
                $data['_attachments'][$field['key']][] = ['name' => basename(parse_url($file, PHP_URL_PATH) ?: $file), 'url' => $file,
                    'path' => $this->media->id($file) ? 'media/'.$this->media->id($file) : 'legacy-media/'.$module.'/'.$record->getKey().'/'.$field['key'].'?index='.$index];
            }
        }
        if ($module === 'beneficiaries') {
            $data['categories'] = DB::table('user_categories')->where('telegram_user_id', $record->getKey())->orderBy('category_id')->pluck('category_id')->all();
            $data['primary_category_id'] = $record->category_id;
            $data['_profile_fields'] = app(AssociationProfile::class)->fields();
            $settings = DB::table('association_profile_settings')->where('beneficiary_id', $record->getKey())->first();
            $data['_profile_settings'] = $settings ? [...(array) $settings, 'editable_fields' => json_decode($settings->editable_fields ?? '[]', true)] : ['editable_fields' => []];
            if ($settings?->personal_photo) { $data['_profile_photo'] = $settings->personal_photo; }
        }
        if ($module === 'events') {
            $settings = DB::table('association_event_settings')->where('event_id', $record->getKey())->first();
            $data = [...$data, 'ends_at' => $this->eventEnd($record)->toIso8601String(),
                'reply_deadline' => $settings?->reply_deadline, 'capacity' => $settings?->capacity,
                'reminder_minutes' => $settings?->reminder_minutes, 'active' => $this->eventActive($record)];
        }
        if (in_array($module, ['invitations', 'attendance', 'deliveries'], true)) {
            $state = DB::table('association_invitation_states')->where('message_log_id', $record->getKey())->first();
            $data['invitation_state'] = $state ? (array) $state : null;
            $data['_invitation_revision'] = $this->operations->revision(['log' => $record->getAttributes(), 'state' => (array) $state]);
        }
        if ($module === 'ramadan-beneficiaries') {
            $data['todays_distribution'] = \App\Models\RamadanDistribution::where('beneficiary_id', $record->id)->whereDate('distribution_date', now()->toDateString())->first()?->getAttributes();
        }
        if ($module === 'meat') {
            $data['receipt'] = (array) DB::table('association_legacy_receipts')->where('module', 'meat')->where('record_id', $record->id)->first();
        }

        return $data;
    }

    public function save(AppAccount $account, string $module, array $input, ?int $id = null): array
    {
        $action = $id === null ? 'create' : 'update';
        $grant = $this->access->require($account, $module, $action);
        Validator::make($input, ['record' => 'required|array|max:300'])->validate();
        if ($module === 'staff') {
            return app(AssociationStaff::class)->save($account, $grant, $input, $id);
        }
        if ($id !== null) {
            $this->records->query($module, $grant, [], true)->whereKey($id)->firstOrFail();
        }
        $this->createScope($grant, $module, $input['record'], $id);

        return $this->operations->run($account, "$module.$action", $input, function () use ($account, $grant, $module, $input, $id): array {
            $record = $id === null ? $this->records->model($module) : $this->records->query($module, $grant)->whereKey($id)->lockForUpdate()->firstOrFail();
            $before = $id === null ? null : $this->records->revisionData($module, $record);
            if ($before !== null) {
                $this->operations->expect($before, $input);
            }
            if ($module === 'ramadan-beneficiaries' && isset($input['record']['national_id']) && is_string($input['record']['national_id'])) {
                $this->identities->lock([$input['record']['national_id']]);
            }
            $data = $this->rules->validate($account, $grant, $module, $input['record'], $id === null ? null : $record);
            if ($module === 'ramadan-distributions' && $id === null) {
                $date = $data['distribution_date'] ?? now()->toDateString();
                abort_unless($date === now()->toDateString(), 422, 'ramadan_receipt_must_be_recorded_today');
                $receipt = app(AssociationRamadan::class)->receive((int) $data['beneficiary_id'], $grant->staff_id, $date);
                abort_unless((int) $receipt['record']->meals_taken === (int) $data['meals_taken'], 422, 'meals_must_match_current_beneficiary_allowance');
                $this->operations->audit($account, $grant, $module, $receipt['record']->id, $receipt['already_applied'] ? 'duplicate' : 'create', null, $receipt['record']->getAttributes());
                return ['record' => $this->describe($module, $receipt['record']), 'already_applied' => $receipt['already_applied']];
            }
            if ($module === 'payments') { \App\Models\User::whereKey($data['user_id'])->lockForUpdate()->firstOrFail(); }
            $categories = $data['categories'] ?? null;
            $primary = $data['primary_category_id'] ?? null;
            $changePrimary = $data['change_main_category'] ?? false;
            unset($data['categories'], $data['primary_category_id'], $data['change_main_category']);
            $eventSettings = [];
            if ($module === 'events') {
                foreach (['ends_at', 'reply_deadline', 'capacity', 'reminder_minutes', 'audience'] as $key) {
                    if (array_key_exists($key, $data)) {
                        $eventSettings[$key] = $data[$key];
                        unset($data[$key]);
                    }
                }
            }
            if ($module === 'beneficiaries' || $module === 'family-members') {
                $identity = $data['national_id'] ?? $record->national_id;
                if ($identity !== null) {
                    $identity = $this->identities->normalize((string) $identity);
                    $this->identities->lock([$identity]);
                    $this->identities->available($identity, $module === 'beneficiaries' ? $id : null, $module === 'family-members' ? $id : null);
                    $data['national_id'] = $identity;
                }
            }
            if ($module === 'family-members') {
                $parent = (int) ($data['telegram_user_id'] ?? $record->telegram_user_id);
                TelegramUser::whereKey($parent)->lockForUpdate()->firstOrFail();
                if ($id !== null && isset($data['telegram_user_id']) && (int) $record->telegram_user_id !== (int) $data['telegram_user_id']) {
                    throw ValidationException::withMessages(['telegram_user_id' => 'لا يمكن نقل فرد الأسرة إلى ملف مختلف بهذا الإجراء.']);
                }
            }
            if ($module === 'beneficiaries' && $id === null) {
                $data['source'] = 'application';
            }
            if ($id === null && str_starts_with($module, 'cases-') && in_array('user_id', array_column($this->records->columns($module), 'name'), true)) {
                $data['user_id'] = $grant->staff_id;
            }
            if ($id !== null && isset(AssociationRecords::PARENTS[$module])) {
                [$foreign] = AssociationRecords::PARENTS[$module];
                abort_if(isset($data[$foreign]) && (int) $data[$foreign] !== (int) $record->$foreign, 422, 'child_record_transfer_requires_separate_action');
            }
            if ($module === 'categories') {
                if ($id === null && ! $this->access->global($grant)) {
                    abort(403, 'category_creation_requires_global_scope');
                }
                if ($id !== null && isset($data['prefix']) && $data['prefix'] !== $record->prefix && (int) $record->last_number > 0) {
                    throw ValidationException::withMessages(['prefix' => 'لا يمكن تغيير بادئة أكواد سبق إصدارها.']);
                }
                if (isset($data['prefix']) && Category::where('prefix', $data['prefix'])->when($id !== null, fn ($query) => $query->whereKeyNot($id))->exists()) {
                    throw ValidationException::withMessages(['prefix' => 'بادئة الأكواد مستخدمة لفئة أخرى.']);
                }
            }
            if ($module === 'events' && $id !== null && ($record->is_archived || ! $this->eventEnd($record)->isFuture())) {
                $oldSettings = DB::table('association_event_settings')->where('event_id', $id)->first();
                $proposedStart = CarbonImmutable::parse(($data['event_date'] ?? $record->event_date).' '.($data['event_time'] ?? $record->event_time ?: '00:00:00'), config('app.timezone'));
                $proposedEnd = array_key_exists('ends_at', $eventSettings)
                    ? ($eventSettings['ends_at'] ? CarbonImmutable::parse($eventSettings['ends_at'], config('app.timezone')) : $proposedStart->addHours(config('association.event_duration_hours')))
                    : ($oldSettings?->ends_at ? CarbonImmutable::parse($oldSettings->ends_at, config('app.timezone')) : $proposedStart->addHours(config('association.event_duration_hours')));
                if (($record->is_archived && array_key_exists('is_archived', $data) && ! $data['is_archived']) || (! ($data['is_archived'] ?? $record->is_archived) && $proposedEnd->isFuture())) {
                    abort(403, 'event_reactivation_requires_reason');
                }
            }
            $columns = array_column($this->records->columns($module), 'name');
            if ($id === null && in_array('created_by', $columns, true)) {
                $data['created_by'] = $grant->staff_id;
            }
            if (in_array('updated_by', $columns, true)) {
                $data['updated_by'] = $grant->staff_id;
            }
            $record->forceFill($data)->save();
            if ($module === 'payments') {
                \App\Http\Controllers\Admin\EmployeePaymentController::processMonthlyPayments($record->user_id, $record->payment_date);
            }
            if ($module === 'beneficiaries' && $categories !== null) {
                $this->categories($grant, $record, $categories, $primary === null ? null : (int) $primary, (bool) $changePrimary);
                $record->refresh();
            }
            if ($module === 'family-members') {
                $this->familyCount((int) $record->telegram_user_id, $grant->staff_id);
            }
            if ($module === 'events') {
                $this->eventSettings($record, $eventSettings);
            }
            $this->media->bind($account, $module, (string) $record->getKey(), $data);
            $this->operations->audit($account, $grant, $module, $record->getKey(), $id === null ? 'create' : 'update', $before, $this->records->revisionData($module, $record->fresh()));

            return ['record' => $this->describe($module, $record->fresh())];
        }, function (array $result) use ($module, $grant): void {
            if (isset($result['record']['id'])) {
                $this->records->query($module, $grant)->whereKey($result['record']['id'])->firstOrFail();
            }
        });
    }

    private function createScope(object $grant, string $module, array $data, ?int $id): void
    {
        if ($id === null && ! $this->access->global($grant) && ! in_array($module, ['beneficiaries', 'events', 'staff'], true)) {
            $scoped = false;
            foreach (['telegram_user_id' => 'beneficiaries', 'family_member_id' => 'family-members', ...isset(AssociationRecords::PARENTS[$module])
                ? [AssociationRecords::PARENTS[$module][0] => AssociationRecords::PARENTS[$module][1]] : []] as $key => $parent) {
                if (isset($data[$key])) { $this->records->query($parent, $grant)->whereKey((int) $data[$key])->firstOrFail(); $scoped = true; }
            }
            if (! $scoped && isset($data['national_id'])) {
                $scoped = $this->access->beneficiaryQuery($grant)->where('national_id', $this->identities->normalize((string) $data['national_id']))->exists();
            }
            abort_unless($scoped, 403, 'record_creation_requires_a_scoped_parent');
        }
        if (! $this->access->global($grant) && $module !== 'beneficiaries' && ! isset(AssociationRecords::PARENTS[$module]) && isset($data['national_id']) &&
            ! in_array('telegram_user_id', array_column($this->records->columns($module), 'name'), true)) {
            abort_unless($this->access->beneficiaryQuery($grant)->where('national_id', $this->identities->normalize((string) $data['national_id']))->exists(), 403, 'record_identity_outside_scope');
        }
        if ($module === 'beneficiaries' && $id === null && ! $this->access->global($grant) && empty($data['categories'])) {
            throw ValidationException::withMessages(['categories' => 'اختر فئة ضمن نطاقك للمستفيد الجديد.']);
        }
        foreach (['telegram_user_id' => 'beneficiaries', 'family_member_id' => 'family-members', 'event_id' => 'events'] as $key => $target) {
            if (isset($data[$key])) {
                $this->records->query($target, $grant)->whereKey((int) $data[$key])->firstOrFail();
            }
        }
        if (isset(AssociationRecords::PARENTS[$module])) {
            [$key, $target] = AssociationRecords::PARENTS[$module];
            if (isset($data[$key])) { $this->records->query($target, $grant)->whereKey((int) $data[$key])->firstOrFail(); }
        }
        if ($module === 'beneficiaries' && isset($data['categories']) && ! $this->access->global($grant) && array_diff($data['categories'], $this->access->categories($grant))) {
            abort(403, 'category_outside_scope');
        }
    }

    private function categories(object $grant, TelegramUser $user, array $ids, ?int $primary, bool $changePrimary): void
    {
        $existing = $user->categories()->pluck('categories.id')->all();
        if (! $this->access->global($grant)) {
            $ids = array_values(array_unique([...$ids, ...array_diff($existing, $this->access->categories($grant))]));
        }
        if ($user->category_id && ! $changePrimary && ! in_array((int) $user->category_id, $ids, true)) {
            $ids[] = (int) $user->category_id;
        }
        if ($changePrimary && ! $ids) {
            throw ValidationException::withMessages(['categories' => 'يلزم اختيار فئة أساسية.']);
        }
        if ($primary !== null && ! in_array($primary, $ids, true)) {
            throw ValidationException::withMessages(['primary_category_id' => 'الفئة الأساسية يجب أن تكون من الفئات المختارة.']);
        }
        $added = array_values(array_diff($ids, $existing));
        if (Category::whereIn('id', $added)->where('is_active', false)->exists()) {
            throw ValidationException::withMessages(['categories' => 'لا يمكن ربط فئة غير مفعّلة.']);
        }
        if ((! $user->access_code || $changePrimary) && $ids) {
            $category = Category::whereKey($primary ?? $ids[0])->lockForUpdate()->firstOrFail();
            $code = $category->generateNextCode();
            $category->increment('last_number');
            if (! in_array($category->id, $added, true)) {
                $category->increment('total_assigned');
            }
            $user->forceFill(['category_id' => $category->id, 'access_code' => $code, 'updated_by' => $grant->staff_id])->save();
        }
        foreach ($added as $category) {
            Category::whereKey($category)->increment('total_assigned');
        }
        $user->categories()->sync($ids);
    }

    public function familyCount(int $beneficiary, int $staff): void
    {
        TelegramUser::whereKey($beneficiary)->update(['family_members' => FamilyMember::where('telegram_user_id', $beneficiary)
            ->whereNotIn('id', DB::table('association_deleted_records')->where('module', 'family-members')->select('record_id'))->count(), 'updated_by' => $staff, 'updated_at' => now()]);
    }

    public function eventEnd(Model $event): CarbonImmutable
    {
        $end = Schema::hasTable('association_event_settings') ? DB::table('association_event_settings')->where('event_id', $event->getKey())->value('ends_at') : null;
        if ($end) {
            return CarbonImmutable::parse($end, config('app.timezone'));
        }

        return CarbonImmutable::parse($event->event_date.' '.($event->event_time ?: '00:00:00'), config('app.timezone'))->addHours(config('association.event_duration_hours'));
    }

    public function eventStart(Model $event): CarbonImmutable
    {
        return CarbonImmutable::parse($event->event_date.' '.($event->event_time ?: '00:00:00'), config('app.timezone'));
    }

    public function eventActive(Model $event): bool
    {
        return ! $event->is_archived && ! $event->getAttribute('deleted_by') && $this->eventStart($event)->lessThanOrEqualTo(now()) && $this->eventEnd($event)->isFuture();
    }

    private function eventSettings(Model $event, array $data): void
    {
        $old = DB::table('association_event_settings')->where('event_id', $event->getKey())->first();
        $start = CarbonImmutable::parse($event->event_date.' '.($event->event_time ?: '00:00:00'), config('app.timezone'));
        $end = array_key_exists('ends_at', $data)
            ? ($data['ends_at'] ? CarbonImmutable::parse($data['ends_at'], config('app.timezone')) : $start->addHours(config('association.event_duration_hours')))
            : ($old?->ends_at ? CarbonImmutable::parse($old->ends_at, config('app.timezone')) : $start->addHours(config('association.event_duration_hours')));
        if (! $end->greaterThan($start)) {
            throw ValidationException::withMessages(['ends_at' => 'نهاية الفعالية يجب أن تكون بعد بدايتها.']);
        }
        $deadline = $data['reply_deadline'] ?? $old?->reply_deadline;
        if ($deadline && CarbonImmutable::parse($deadline)->greaterThan($end)) {
            throw ValidationException::withMessages(['reply_deadline' => 'مهلة الرد يجب أن تسبق نهاية الفعالية.']);
        }
        $row = [...$data, 'ends_at' => $end->format('Y-m-d H:i:s'), 'reply_deadline' => $deadline ? CarbonImmutable::parse($deadline)->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s') : null, 'updated_at' => now()];
        if (isset($row['audience'])) {
            $row['audience'] = json_encode($row['audience'], JSON_THROW_ON_ERROR);
        }
        if (! $old) {
            $row['created_at'] = now();
        }
        DB::table('association_event_settings')->updateOrInsert(['event_id' => $event->getKey()], $row);
    }
}
