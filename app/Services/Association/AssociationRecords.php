<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/** Legacy models are an explicit allowlist; clients never supply a table or SQL. */
final class AssociationRecords
{
    public const PARENTS = ['case-deceased' => ['guardian_id', 'cases-guardians'], 'cases-orphans' => ['guardian_id', 'cases-guardians'],
        'case-breadwinner-members' => ['family_id', 'cases-breadwinners'], 'case-stranded-members' => ['stranded_family_id', 'cases-stranded'],
        'case-housing-members' => ['solidarity_housing_id', 'cases-housing'], 'case-injured-members' => ['injured_case_id', 'cases-injured'],
        'case-patient-members' => ['patient_case_id', 'cases-patients'], 'guardian-message-history' => ['guardian_id', 'cases-guardians'],
        'amputation-message-history' => ['amputation_id', 'cases-amputations'], 'housing-message-history' => ['solidarity_housing_id', 'cases-housing'],
        'breadwinner-message-history' => ['family_id', 'cases-breadwinners'], 'stranded-message-history' => ['stranded_family_id', 'cases-stranded'],
        'injured-message-history' => ['injured_case_id', 'cases-injured'], 'patient-message-history' => ['patient_case_id', 'cases-patients'],
        'ramadan-distributions' => ['beneficiary_id', 'ramadan-beneficiaries']];
    public const SYSTEM_FIELDS = ['id', 'created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by', 'deleted_by',
        'sent_by', 'sender_type', 'attended_at', 'attended_by', 'excused_at', 'excused_by', 'reviewed_by', 'reviewed_at',
        'approved_by', 'benefit_count', 'access_code', 'last_number', 'total_assigned', 'source', 'ip_address',
        'spam_count', 'is_processing', 'last_message_at', 'after_completed_messages_count', 'replied_once', 'step',
        'current_family_member_index', 'last_family_member_name', 'override_by', 'is_override', 'case_code',
        'is_reviewed', 'is_sent_for_attendance', 'is_attended', 'is_benefited', 'researcher_id'];

    public const SECRET_FIELDS = ['password', 'remember_token', 'api_key', 'api_secret', 'token', 'access_token', 'refresh_token'];

    private const LABELS = [
        'name' => 'الاسم', 'full_name' => 'الاسم الكامل', 'first_name' => 'الاسم الأول', 'title' => 'العنوان',
        'name_en' => 'الاسم بالإنجليزية', 'title_en' => 'العنوان بالإنجليزية', 'key' => 'المعرّف',
        'national_id' => 'رقم الهوية', 'birth_date' => 'تاريخ الميلاد', 'age' => 'العمر', 'gender' => 'الجنس',
        'phone' => 'رقم الهاتف', 'phone_egypt' => 'رقم الهاتف المصري', 'phone_egypt_2' => 'رقم هاتف إضافي',
        'whatsapp' => 'واتساب', 'whatsapp_palestine' => 'واتساب فلسطين', 'telegram_number' => 'رقم تيليجرام',
        'egypt_number' => 'الرقم المصري', 'email' => 'البريد الإلكتروني', 'password' => 'كلمة المرور',
        'address' => 'العنوان', 'governorate' => 'المحافظة', 'city' => 'المدينة', 'area_id' => 'المنطقة',
        'city_id' => 'المدينة', 'governorate_id' => 'المحافظة', 'category_id' => 'الفئة الأساسية',
        'case_profile_id' => 'نوع الحالة', 'categories' => 'الفئات', 'prefix' => 'بادئة الكود',
        'is_active' => 'مفعّل', 'active' => 'مفعّل', 'is_approved' => 'معتمد', 'status' => 'الحالة',
        'is_completed' => 'مكتمل', 'is_blocked' => 'عليه ملاحظة تمنع الإرسال التلقائي', 'blocked_reason' => 'سبب الملاحظة',
        'telegram_user_id' => 'المستفيد', 'family_member_id' => 'فرد الأسرة', 'event_id' => 'الفعالية',
        'guardian_id' => 'الوصي', 'orphan_id' => 'ملف اليتيم', 'amputation_case_id' => 'ملف البتر',
        'family_members' => 'عدد أفراد الأسرة', 'family_members_count' => 'عدد أفراد الأسرة',
        'notes' => 'ملاحظات', 'note' => 'الملاحظة', 'admin_note' => 'ملاحظة الإدارة',
        'description' => 'الوصف', 'description_en' => 'الوصف بالإنجليزية', 'message' => 'الرسالة',
        'message_type' => 'نوع الرسالة', 'delivery_status' => 'حالة الإرسال', 'sent_at' => 'وقت الإرسال',
        'event_date' => 'تاريخ الفعالية', 'event_time' => 'وقت الفعالية', 'location' => 'المكان',
        'invitation_message' => 'نص الدعوة', 'default_message' => 'نص رسالة التسليم',
        'aid_type' => 'نوع المساعدة', 'value_type' => 'نوع القيمة', 'value' => 'القيمة', 'value_text' => 'وصف القيمة',
        'extras' => 'تفاصيل إضافية', 'is_archived' => 'مؤرشفة', 'is_fixed' => 'فعالية ثابتة',
        'without_notification' => 'تسجيل دون إشعار خارجي', 'day_of_month' => 'يوم الشهر',
        'completed_for_month' => 'شهر الإتمام', 'relation' => 'صلة القرابة', 'relationship' => 'صلة القرابة',
        'relationship_to_case' => 'صلة القرابة بالحالة', 'is_orphan' => 'يتيم', 'orphan_status' => 'حالة اليتم',
        'housing_status' => 'حالة السكن', 'marital_status' => 'الحالة الاجتماعية', 'monthly_income' => 'الدخل الشهري',
        'entry_date' => 'تاريخ الدخول', 'entry_method' => 'طريقة الدخول', 'entry_reason' => 'سبب الدخول',
        'entry_to_egypt' => 'تاريخ الدخول إلى مصر', 'rent_amount' => 'قيمة الإيجار', 'rent_value' => 'قيمة الإيجار',
        'landlord_phone' => 'هاتف المؤجّر', 'latitude' => 'خط العرض', 'longitude' => 'خط الطول',
        'personal_photo' => 'الصورة الشخصية', 'person_image' => 'الصورة الشخصية', 'passport' => 'جواز السفر',
        'guardian_passport' => 'جواز سفر الوصي', 'id_document' => 'وثيقة الهوية', 'entry_stamp' => 'ختم الدخول',
        'medical_reports' => 'التقارير الطبية', 'medical_report' => 'التقرير الطبي', 'rent_contract' => 'عقد الإيجار',
        'death_certificate' => 'شهادة الوفاة', 'birth_certificate' => 'شهادة الميلاد', 'amputation_image' => 'صورة البتر',
        'amputation_type' => 'نوع البتر', 'amputation_date' => 'تاريخ البتر', 'injury_reason' => 'سبب الإصابة',
        'needs_prosthetic' => 'بحاجة إلى طرف صناعي', 'received_help' => 'سبق أن تلقّى مساعدة', 'help_type' => 'نوع المساعدة السابقة',
        'health_description' => 'وصف الحالة الصحية', 'has_income' => 'لديه دخل', 'needs_companion' => 'بحاجة إلى مرافق',
        'companion_full_name' => 'اسم المرافق', 'companion_national_id' => 'هوية المرافق', 'companion_birth_date' => 'ميلاد المرافق',
        'companion_relationship' => 'صلة المرافق', 'target_case' => 'نوع الحالة', 'disease_type' => 'نوع المرض',
        'benefited_before' => 'سبق أن استفاد', 'approval_description' => 'وصف الاعتماد', 'lives_with_guardian' => 'يقيم مع الوصي',
        'education_level' => 'المستوى التعليمي', 'study_type' => 'نوع الدراسة', 'has_chronic_disease' => 'لديه مرض مزمن',
        'has_disability' => 'لديه إعاقة', 'disability_type' => 'نوع الإعاقة', 'disability_description' => 'وصف الإعاقة',
        'passport_number' => 'رقم جواز السفر', 'health_status' => 'الحالة الصحية', 'role' => 'المهمة',
        'basic_salary' => 'الراتب الأساسي', 'amount' => 'المبلغ', 'date' => 'التاريخ', 'payment_date' => 'تاريخ الدفعة',
        'user_id' => 'الموظف', 'type' => 'النوع', 'code' => 'الكود', 'attended' => 'حضر', 'daily_meals' => 'الوجبات اليومية',
        'beneficiary_id' => 'المستفيد', 'distribution_date' => 'تاريخ التوزيع', 'meals_taken' => 'الوجبات المستلمة',
        'want_tripe' => 'طلب أحشاء', 'want_legs' => 'طلب أرجل', 'separate_family' => 'أسرة مستقلة',
        'received_before' => 'سبق أن استلم', 'received_wheelchair' => 'تم تسليم الكرسي', 'has_wheelchair' => 'لديه كرسي',
        'wheelchair_status' => 'حالة الكرسي', 'organization_name' => 'الجهة السابقة', 'medical_condition' => 'الحالة الطبية',
        'rejection_reason' => 'سبب الرفض', 'delivered_at' => 'وقت التسليم', 'disability_photo' => 'صورة الإعاقة',
        'passport_photo' => 'صورة جواز السفر', 'entry_stamp_photo' => 'صورة ختم الدخول', 'photographer_ad_id' => 'إعلان التصوير',
        'camera_equipment' => 'معدات التصوير', 'family_in_egypt' => 'أفراد الأسرة في مصر', 'family_in_gaza' => 'أفراد الأسرة في غزة',
        'education' => 'التعليم', 'work_in_gaza' => 'العمل في غزة', 'has_experience' => 'لديه خبرة',
        'experience_details' => 'تفاصيل الخبرة', 'case_details' => 'تفاصيل الحالة', 'username' => 'اسم المستخدم',
    ];

    public function __construct(private AssociationAccess $access, private AssociationOperations $operations) {}

    public function model(string $module): Model
    {
        $definition = AssociationModules::definition($module);
        abort_unless($definition['model'] !== null, 404);
        $class = 'App\\Models\\'.$definition['model'];
        abort_unless(class_exists($class) && is_subclass_of($class, Model::class), 404);
        $model = new $class;
        abort_unless(Schema::hasTable($model->getTable()), 503, 'association_legacy_table_required');

        return $model;
    }

    public function columns(string $module): array
    {
        return Schema::getColumns($this->model($module)->getTable());
    }

    public function query(string $module, object $grant, array $filter = [], bool $includeDeleted = false)
    {
        $model = $this->model($module);
        $table = $model->getTable();
        $columns = array_column($this->columns($module), 'name');
        $query = $model->newQuery();
        if ($includeDeleted) { $query->withoutGlobalScope('association_deleted'); }
        if (! $includeDeleted) {
            $query->whereNotIn($table.'.id', DB::table('association_deleted_records')->where('module', $module)->select('record_id'));
        }
        if ($includeDeleted && in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($model), true)) {
            $query->withTrashed();
        }
        if (! $includeDeleted && in_array('deleted_by', $columns, true)) {
            $query->whereNull($table.'.deleted_by');
        }
        if (! $this->access->global($grant)) {
            $beneficiaries = $this->access->beneficiaryQuery($grant, $includeDeleted && $module === 'beneficiaries');
            if (isset(self::PARENTS[$module])) {
                [$foreign, $parent] = self::PARENTS[$module];
                $parentQuery = $this->query($parent, $grant);
                $query->whereIn($table.'.'.$foreign, $parentQuery->select($parentQuery->getModel()->getTable().'.id'));
            } elseif ($module === 'beneficiaries') {
                $query->whereIn($table.'.id', $beneficiaries->select('telegram_users.id'));
            } elseif ($module === 'categories') {
                $query->whereIn($table.'.id', $this->access->categories($grant));
            } elseif (in_array('telegram_user_id', $columns, true)) {
                $query->whereIn($table.'.telegram_user_id', $beneficiaries->select('telegram_users.id'));
            } elseif (in_array('family_member_id', $columns, true)) {
                $query->whereIn($table.'.family_member_id', DB::table('family_members')->whereIn('telegram_user_id', $beneficiaries->select('telegram_users.id'))->select('id'));
            } elseif (in_array('national_id', $columns, true)) {
                $query->whereIn($table.'.national_id', $beneficiaries->select('telegram_users.national_id'));
            } elseif ($module === 'events') {
                $query->where(function ($query) use ($grant, $table, $columns, $beneficiaries): void {
                    $query->whereIn($table.'.id', DB::table('message_logs')->whereNull('deleted_at')->whereIn('telegram_user_id', $beneficiaries->select('telegram_users.id'))->select('event_id'));
                    if (in_array('created_by', $columns, true)) {
                        $query->orWhere($table.'.created_by', $grant->staff_id);
                    }
                });
            } else {
                // Never turn an unsupported scope into unrestricted legacy data.
                $query->whereRaw('1 = 0');
            }
        }
        if (in_array($module, ['invitations', 'attendance', 'deliveries'], true)) {
            $query->where('message_type', 'invitation')->whereIn('delivery_status', ['sent', 'manual']);
        }
        foreach (['event_id', 'telegram_user_id', 'family_member_id', 'category_id', 'beneficiary_id', 'user_id', ...array_keys(AssociationRecordRules::LOOKUPS)] as $key) {
            if (isset($filter[$key]) && in_array($key, $columns, true)) {
                $query->where($table.'.'.$key, (int) $filter[$key]);
            }
        }
        if (isset($filter['status']) && $filter['status'] !== '' && in_array('status', $columns, true)) {
            $query->where($table.'.status', $filter['status']);
        }
        if (! empty($filter['q'])) {
            $fields = array_values(array_intersect(['full_name', 'name', 'title', 'national_id', 'phone', 'whatsapp', 'egypt_number', 'email', 'code', 'access_code', 'message'], $columns));
            $search = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_substr(trim($filter['q']), 0, 120)).'%';
            if ($fields) {
                $query->where(function ($query) use ($fields, $search, $table): void {
                    foreach ($fields as $field) {
                        $query->orWhere($table.'.'.$field, 'like', $search);
                    }
                });
            }
        }

        return $query;
    }

    public function serialize(object|array $record, ?string $module = null): array
    {
        $raw = $record instanceof Model ? $record->getAttributes() : (array) $record;
        $data = $record instanceof Model ? $record->attributesToArray() : (array) $record;
        $data = array_diff_key($data, array_flip(self::SECRET_FIELDS));
        $data['_revision'] = $this->operations->revision($module !== null && $record instanceof Model ? $this->revisionData($module, $record) : $raw);
        $label = $data['full_name'] ?? $data['title'] ?? $data['name'] ?? $data['message'] ?? $data['national_id'] ?? $data['id'] ?? '';
        $localized = is_array($label) ? $label : (is_string($label) ? json_decode($label, true) : null);
        $data['_label'] = is_array($localized) ? (string) ($localized[app()->getLocale()] ?? $localized['ar'] ?? $localized['en'] ?? $data['id']) : (string) $label;
        foreach ($data as $key => $value) {
            if (is_string($value) && (str_ends_with($key, '_at') || $key === 'created_at' || $key === 'updated_at') && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/D', $value)) {
                $data[$key] = \Carbon\CarbonImmutable::parse($value, config('app.timezone'))->toIso8601String();
            }
        }

        return $data;
    }

    public function revisionData(string $module, Model $record): array
    {
        $data = ['record' => $record->getAttributes()];
        if ($module === 'staff') {
            $data['authorization'] = app(AssociationStaff::class)->details($record);
        }
        if ($module === 'beneficiaries' && Schema::hasTable('user_categories')) {
            $data['categories'] = DB::table('user_categories')->where('telegram_user_id', $record->getKey())->orderBy('category_id')->pluck('category_id')->all();
        }
        if ($module === 'events' && Schema::hasTable('association_event_settings')) {
            $data['settings'] = (array) DB::table('association_event_settings')->where('event_id', $record->getKey())->first();
        }

        return $data;
    }

    public function fields(string $module): array
    {
        if ($module === 'staff') { return app(AssociationStaff::class)->fields(); }
        $fields = [];
        foreach ($this->columns($module) as $column) {
            $key = $column['name'];
            if (in_array($key, self::SECRET_FIELDS, true) || in_array($key, self::SYSTEM_FIELDS, true)) {
                continue;
            }
            $type = $column['type_name'];
            $fieldType = match (true) {
                str_contains($type, 'bool'), str_starts_with($key, 'is_'), str_starts_with($key, 'has_'), in_array($key, ['active', 'attended', 'without_notification', 'needs_prosthetic', 'received_help', 'needs_companion', 'lives_with_guardian', 'want_tripe', 'want_legs', 'separate_family', 'benefited_before', 'received_before', 'received_wheelchair'], true) => 'bool',
                $type === 'time', str_ends_with($key, '_time') => 'time',
                str_contains($type, 'datetime'), str_contains($type, 'timestamp'), str_ends_with($key, '_at') => 'datetime',
                $type === 'date', str_ends_with($key, '_date'), $key === 'date' => 'date',
                preg_match('/photo|image|passport(?!_number)|certificate|contract|medical_report|attachment|id_document|entry_stamp/', $key) === 1,
                in_array($key, ['case_id', 'guardian_id'], true) && ! preg_match('/int|decimal|numeric/', $type) => 'file',
                str_contains($type, 'json') => 'json',
                preg_match('/int|decimal|numeric|double|float|real/', $type) === 1 && $key !== 'national_id' => 'number',
                str_contains($type, 'text') => 'textarea',
                default => 'text',
            };
            $field = ['key' => $key, 'label' => self::LABELS[$key] ?? Str::headline($key), 'label_en' => Str::headline($key),
                'type' => $fieldType, 'required' => ! $column['nullable'] && $column['default'] === null,
                'nullable' => $column['nullable'], 'default' => $column['default']];
            if (isset(AssociationRecordRules::LOOKUPS[$key]) && preg_match('/int/', $type)) {
                $field['type'] = 'lookup';
                $field['lookup'] = AssociationRecordRules::LOOKUPS[$key];
            }
            if (preg_match_all("/'([^']*)'/", $column['type'], $options) && str_starts_with($column['type_name'], 'enum')) {
                $field['type'] = 'select';
                $field['options'] = array_map(fn ($value) => ['value' => $value, 'label' => $this->statusLabel($value), 'label_en' => Str::headline($value)], $options[1]);
            }
            if ($key === 'status' && in_array($module, ['orphan-requests', 'amputation-requests', 'wheelchair-requests', 'photographers'], true)) {
                $field['readonly'] = true;
            }
            if ($module === 'beneficiaries' && in_array($key, ['category_id', 'family_members', 'chat_id', 'business_connection_id', 'is_approved', 'approval_description'], true)) {
                $field['readonly'] = true;
            }
            if (str_starts_with($module, 'cases-') && $key === 'user_id') { $field['readonly'] = true; }
            if (str_starts_with($module, 'cases-') && in_array($key, ['case_description', 'researcher_opinion', 'family_deserves', 'family_reason', 'deserves_support', 'deserve_reason'], true)) { $field['readonly'] = true; }
            if ($module === 'embassy' && $key === 'attended') { $field['readonly'] = true; }
            if (in_array($module, ['invitations', 'attendance', 'deliveries', 'message-log'], true) && in_array($key, ['delivery_status', 'message_type', 'event_id', 'telegram_user_id'], true)) {
                $field['readonly'] = true;
            }
            $fields[] = $field;
        }
        if ($module === 'beneficiaries') {
            $fields[] = ['key' => 'categories', 'label' => 'الفئات', 'label_en' => 'Categories', 'type' => 'lookup_many', 'lookup' => 'categories'];
            $fields[] = ['key' => 'change_main_category', 'label' => 'تغيير الفئة الأساسية وإصدار كود جديد', 'label_en' => 'Change primary category and issue a new code', 'type' => 'bool'];
            $fields[] = ['key' => 'primary_category_id', 'label' => 'الفئة الأساسية الجديدة', 'label_en' => 'New primary category', 'type' => 'lookup', 'lookup' => 'categories'];
        }
        if ($module === 'events') {
            $fields[] = ['key' => 'ends_at', 'label' => 'نهاية الفعالية', 'label_en' => 'Event ends', 'type' => 'datetime'];
            $fields[] = ['key' => 'reply_deadline', 'label' => 'مهلة الرد على الدعوة', 'label_en' => 'Reply deadline', 'type' => 'datetime'];
            $fields[] = ['key' => 'capacity', 'label' => 'سعة الفعالية', 'label_en' => 'Capacity', 'type' => 'number'];
            $fields[] = ['key' => 'reminder_minutes', 'label' => 'التذكير قبل الموعد بالدقائق', 'label_en' => 'Reminder lead time in minutes', 'type' => 'number'];
        }

        return $fields;
    }

    public function statusLabel(string $value): string
    {
        return ['pending' => 'قيد المراجعة', 'approved' => 'مقبول', 'rejected' => 'مرفوض', 'completed' => 'مكتمل',
            'blocked' => 'عليه ملاحظة', 'active' => 'مفعّل', 'inactive' => 'غير مفعّل', 'sent' => 'أُرسل', 'manual' => 'مسجّل',
            'invitation' => 'دعوة', 'received' => 'تسليم', 'general' => 'عامة', 'in_kind' => 'عينية', 'cash' => 'نقدية',
            'other' => 'أخرى', 'number' => 'رقم', 'text' => 'نص', 'male' => 'ذكر', 'female' => 'أنثى',
            'approved_first' => 'مقبول — المرحلة الأولى', 'approved_second' => 'مقبول — المرحلة الثانية', 'approved_third' => 'مقبول — المرحلة الثالثة',
            'rejected_previous' => 'مرفوض — سبق الاستفادة', 'rejected_not_eligible' => 'مرفوض — غير مستحق'][$value] ?? Str::headline($value);
    }
}
