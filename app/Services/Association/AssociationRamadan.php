<?php

namespace App\Services\Association;

use App\Models\RamadanBeneficiary;
use App\Models\RamadanDistribution;
use Illuminate\Support\Facades\DB;

final class AssociationRamadan
{
    public function receive(int $beneficiary, int $staff, string $date): array
    {
        return DB::transaction(function () use ($beneficiary, $staff, $date): array {
            $person = RamadanBeneficiary::whereKey($beneficiary)->lockForUpdate()->firstOrFail();
            $existing = RamadanDistribution::where('beneficiary_id', $beneficiary)->whereDate('distribution_date', $date)->first();
            if ($existing) { return ['record' => $existing, 'already_applied' => true]; }
            abort_unless((int) $person->daily_meals > 0, 422, 'daily_meals_must_be_positive');
            $record = RamadanDistribution::create(['beneficiary_id' => $person->id, 'distribution_date' => $date, 'meals_taken' => $person->daily_meals, 'created_by' => $staff]);
            return ['record' => $record, 'already_applied' => false];
        }, 3);
    }
}
