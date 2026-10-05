<?php

namespace App\Imports;

use App\Models\RamadanBeneficiary;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BeneficiariesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // تجاهل الصف إذا لا يوجد رقم هوية
        if (empty($row['national_id'])) {
            return null;
        }

        return RamadanBeneficiary::updateOrCreate(
            [
                'national_id' => trim($row['national_id']),
            ],
            [
                'full_name'            => $row['full_name'] ?? 'غير محدد',
                'code'                 => $row['code'] ?? null,
                'family_members_count' => $row['family_members_count'] ?? 0,
                'daily_meals'          => $row['daily_meals'] ?? 1,
            ]
        );
    }
}