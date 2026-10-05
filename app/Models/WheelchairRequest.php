<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WheelchairRequest extends Model
{
    protected $fillable = [
    'full_name',
    'national_id',
    'phone',
    'whatsapp',
    'personal_photo',
    'disability_photo',
    'passport_photo',
    'entry_stamp_photo',
    'medical_report',
    'age',
    'address',
    'entry_date',
    'medical_condition',
    'injury_reason',
    'has_wheelchair',
    'wheelchair_status',
    'received_before',
    'organization_name',
    'notes',
    'status',
    'reviewed_by',
    'reviewed_at',
    'rejection_reason',
    'received_wheelchair',
    'delivered_at',
];
public function reviewer()
{
    return $this->belongsTo(User::class, 'reviewed_by');
}
}
