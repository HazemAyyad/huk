<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class AssociationLegacyActions
{
    private const HISTORY = ['cases-guardians' => ['GuardianMessage', 'guardian_id'], 'cases-amputations' => ['AmputationMessage', 'amputation_id'],
        'cases-housing' => ['SolidarityHousingMessage', 'solidarity_housing_id'], 'cases-breadwinners' => ['FamilyBreadwinnerMessage', 'family_id'],
        'cases-stranded' => ['StrandedFamilyMessage', 'stranded_family_id'], 'cases-injured' => ['InjuredCaseMessage', 'injured_case_id'], 'cases-patients' => ['PatientCaseMessage', 'patient_case_id']];

    public function __construct(private AssociationAccess $access, private AssociationRecords $records, private AssociationOperations $operations, private AssociationEntities $entities) {}

    public function run(AppAccount $account, string $module, int $id, array $input): array
    {
        $action = $input['action'] ?? '';
        Validator::make($input, ['reason' => 'sometimes|nullable|string|max:10000'])->validate();
        $grant = $this->access->require($account, $module, $action);
        $this->records->query($module, $grant)->whereKey($id)->firstOrFail();
        return $this->operations->run($account, "$module.$id.$action", $input, function () use ($account, $grant, $module, $id, $input, $action): array {
            $record = $this->records->query($module, $grant)->whereKey($id)->lockForUpdate()->firstOrFail();
            $before = $this->records->revisionData($module, $record);
            $this->operations->expect($before, $input);
            $already = false;
            if (isset(self::HISTORY[$module]) && in_array($action, ['invite', 'attend', 'benefit'], true)) {
                $data = Validator::make($input, ['reason' => 'required|string|min:3|max:10000', 'latitude' => 'nullable|numeric|between:-90,90', 'longitude' => 'nullable|numeric|between:-180,180'])->validate();
                $field = ['invite' => 'is_sent_for_attendance', 'attend' => 'is_attended', 'benefit' => 'is_benefited'][$action];
                abort_unless(in_array($field, array_column($this->records->columns($module), 'name'), true), 422, 'legacy_action_not_supported');
                $already = (bool) $record->$field;
                if (! $already) {
                    $record->forceFill([$field => true])->save();
                    [$model, $foreign] = self::HISTORY[$module]; $class = 'App\\Models\\'.$model;
                    $history = new $class;
                    $history->forceFill([$foreign => $id, 'user_id' => $grant->staff_id, 'message' => $data['reason'], 'latitude' => $data['latitude'] ?? null, 'longitude' => $data['longitude'] ?? null])->save();
                }
            } elseif ($module === 'ramadan-beneficiaries' && $action === 'confirm') {
                $receipt = app(AssociationRamadan::class)->receive($id, $grant->staff_id, now()->toDateString());
                $already = $receipt['already_applied'];
            } elseif ($module === 'embassy' && $action === 'confirm') {
                $already = (bool) $record->attended;
                if (! $already) { $record->forceFill(['attended' => true])->save(); }
            } elseif ($module === 'meat' && $action === 'confirm') {
                $already = DB::table('association_legacy_receipts')->where('module', $module)->where('record_id', $id)->exists();
                if (! $already) { DB::table('association_legacy_receipts')->insert(['module' => $module, 'record_id' => $id, 'staff_id' => $grant->staff_id, 'received_at' => now(), 'note' => $input['reason'] ?? null]); }
            } else { abort(422, 'unsupported_legacy_action'); }
            $this->operations->audit($account, $grant, $module, $id, $already ? 'duplicate' : $action, $before, $this->records->revisionData($module, $record->fresh()), $input['reason'] ?? null);

            return ['record' => $this->entities->describe($module, $record->fresh()), 'already_applied' => $already];
        }, fn () => $this->records->query($module, $grant)->whereKey($id)->firstOrFail());
    }
}
