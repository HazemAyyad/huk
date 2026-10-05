<?php

namespace App\Imports;

use App\Models\EmbassyEventAttendee;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmbassyEventAttendeesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {

        // تجاهل الصفوف الفارغة
        if(empty($row['type']) && empty($row['full_name'])){
            return null;
        }

        return new EmbassyEventAttendee([
            'type'        => trim($row['type']),
            'code'        => isset($row['code']) ? (string)$row['code'] : null,
            'full_name'   => trim($row['full_name']),
            'national_id' => isset($row['national_id']) ? (string)$row['national_id'] : null,
            'notes'       => $row['notes'] ?? null,
            'created_by'  => Auth::id(),
        ]);
    }
}
