<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class AssociationActions
{
    public function __construct(private AssociationAccess $access, private AssociationRecords $records,
        private AssociationEntities $entities, private AssociationOperations $operations) {}

    public function run(AppAccount $account, string $module, int $id, array $input): array
    {
        Validator::make($input, ['action' => 'required|string|max:30'])->validate();
        $action = $input['action'];
        if (in_array($action, ['invite', 'attend', 'benefit', 'confirm'], true)) {
            return app(AssociationLegacyActions::class)->run($account, $module, $id, $input);
        }
        $grant = $this->access->require($account, $module, $action);
        abort_unless(in_array($action, ['delete', 'restore', 'review', 'reactivate', 'complete'], true), 422, 'unsupported_record_action');
        $this->records->query($module, $grant, [], $action === 'restore')->whereKey($id)->firstOrFail();

        return $this->operations->run($account, "$module.$id.$action", $input, function () use ($account, $grant, $module, $id, $action, $input): array {
            $record = $this->records->query($module, $grant, [], $action === 'restore')->whereKey($id)->lockForUpdate()->firstOrFail();
            $before = $this->records->revisionData($module, $record);
            $this->operations->expect($before, $input);
            $columns = array_column($this->records->columns($module), 'name');
            if ($action === 'reactivate') {
                abort_unless($module === 'events', 422);
                $data = Validator::make($input, ['reason' => 'required|string|min:3|max:2000', 'ends_at' => 'required|date|after:now'])->validate();
                $record->forceFill(['is_archived' => false, 'updated_by' => $grant->staff_id])->save();
                DB::table('association_event_settings')->updateOrInsert(['event_id' => $id], ['ends_at' => \Carbon\CarbonImmutable::parse($data['ends_at'])->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                    'reactivated_at' => now(), 'reactivated_by' => $grant->staff_id, 'reactivation_reason' => $data['reason'], 'updated_at' => now()]);
            } elseif ($action === 'delete') {
                if ($module === 'staff') {
                    abort_unless($grant->is_superadmin && $id !== (int) $account->user_id, 403, 'cannot_delete_own_staff');
                    DB::table('app_accounts')->where('user_id', $id)->update(['active' => false, 'updated_at' => now()]);
                }
                DB::table('association_deleted_records')->insertOrIgnore(['module' => $module, 'record_id' => (string) $id, 'staff_id' => $grant->staff_id, 'created_at' => now()]);
                if (in_array('deleted_by', $columns, true)) { $record->forceFill(['deleted_by' => $grant->staff_id])->save(); }
                if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($record), true)) { $record->delete(); }
                if ($module === 'family-members') {
                    // Retain the original row for history while excluding its tombstone from family size.
                    $count = $this->records->query('family-members', $grant, ['telegram_user_id' => $record->telegram_user_id])->count();
                    DB::table('telegram_users')->where('id', $record->telegram_user_id)->update(['family_members' => $count, 'updated_by' => $grant->staff_id, 'updated_at' => now()]);
                }
            } elseif ($action === 'restore') {
                DB::table('association_deleted_records')->where('module', $module)->where('record_id', (string) $id)->delete();
                if (in_array('deleted_by', $columns, true)) { $record->forceFill(['deleted_by' => null])->save(); }
                if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($record), true)) { $record->restore(); }
            } elseif ($action === 'complete') {
                abort_unless($module === 'reminders', 422);
                $record->forceFill(['completed_for_month' => now()->format('Y-m')])->save();
            } elseif ($action === 'review') {
                if (str_starts_with($module, 'cases-')) {
                    $rules = [];
                    foreach (app(AssociationActionForms::class)->fields($module, 'review') as $field) {
                        $rules[$field['key']] = $field['type'] === 'select'
                            ? ($field['key'] === 'deserves_support' ? ['required', 'boolean'] : ['required', 'in:yes,no'])
                            : ['required', 'string', 'min:1', 'max:10000'];
                    }
                    $values = Validator::make($input, $rules)->validate();
                    $record->forceFill([...$values, 'is_reviewed' => true, 'researcher_id' => $grant->staff_id])->save();
                } else {
                $statuses = $module === 'wheelchair-requests' ? ['pending', 'approved_first', 'approved_second', 'approved_third', 'rejected_previous', 'rejected_not_eligible'] : ['approved', 'rejected'];
                $data = Validator::make($input, ['status' => ['required', \Illuminate\Validation\Rule::in($statuses)], 'reason' => 'nullable|string|max:5000'])->validate();
                if (str_starts_with($data['status'], 'rejected')) { Validator::make($data, ['reason' => 'required|string|min:3'])->validate(); }
                $values = [];
                if ($module === 'beneficiaries') {
                    $values = ['is_approved' => $data['status'] === 'approved', 'approval_description' => $data['reason'] ?? null, 'approved_by' => $grant->staff_id];
                } elseif (in_array($module, ['orphan-requests', 'amputation-requests', 'wheelchair-requests', 'photographers'], true)) {
                    $values = ['status' => $data['status'], 'reviewed_at' => now(), 'reviewed_by' => $grant->staff_id];
                    $values[$module === 'wheelchair-requests' ? 'rejection_reason' : 'admin_note'] = $data['reason'] ?? null;
                } else { abort(422, 'unsupported_review'); }
                $record->forceFill(array_intersect_key($values, array_flip($columns)))->save();
                }
            }
            $this->operations->audit($account, $grant, $module, $id, $action, $before, $this->records->revisionData($module, $record->fresh()), $input['reason'] ?? null);

            return ['id' => $id, 'action' => $action, ...($action === 'delete' ? [] : ['record' => $this->entities->describe($module, $record->fresh())])];
        }, function () use ($grant, $module, $id): void {
            $this->records->query($module, $grant, [], true)->whereKey($id)->firstOrFail();
        });
    }
}
