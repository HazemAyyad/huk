<?php

namespace App\Services\Association;

final class AssociationActionForms
{
    public function __construct(private AssociationRecords $records) {}

    public function fields(string $module, string $action): array
    {
        $text = fn ($key, $label, $required = true) => ['key' => $key, 'label' => $label, 'label_en' => \Illuminate\Support\Str::headline($key), 'type' => 'textarea', 'required' => $required];
        if ($action === 'review' && str_starts_with($module, 'cases-')) {
            $columns = array_column($this->records->columns($module), 'name');
            $boolean = in_array('deserves_support', $columns, true);
            return [$text('case_description', 'وصف الحالة'), $text('researcher_opinion', 'رأي الباحث'),
                ['key' => $boolean ? 'deserves_support' : 'family_deserves', 'label' => 'استحقاق المساعدة', 'label_en' => 'Assistance eligibility', 'type' => 'select', 'required' => true,
                    'options' => [['value' => $boolean ? true : 'yes', 'label' => 'تستحق', 'label_en' => 'Eligible'], ['value' => $boolean ? false : 'no', 'label' => 'لا تستحق', 'label_en' => 'Not eligible']]],
                $text($boolean ? 'deserve_reason' : 'family_reason', 'سبب قرار الاستحقاق')];
        }
        if ($action === 'review') {
            $statuses = $module === 'wheelchair-requests' ? ['pending', 'approved_first', 'approved_second', 'approved_third', 'rejected_previous', 'rejected_not_eligible'] : ['approved', 'rejected'];
            return [['key' => 'status', 'label' => 'قرار المراجعة', 'label_en' => 'Review decision', 'type' => 'select', 'required' => true,
                'options' => array_map(fn ($value) => ['value' => $value, 'label' => $this->records->statusLabel($value), 'label_en' => \Illuminate\Support\Str::headline($value)], $statuses)],
                $text('reason', 'ملاحظة المراجعة / سبب الرفض', false)];
        }
        if (in_array($action, ['invite', 'attend', 'benefit'], true) && str_starts_with($module, 'cases-')) {
            return [$text('reason', 'ملاحظة الإجراء')];
        }
        if ($action === 'reactivate') { return [$text('reason', 'سبب إعادة التفعيل')]; }
        if ($action === 'confirm' && $module === 'meat') { return [$text('reason', 'ملاحظة الاستلام', false)]; }
        return [];
    }
}
