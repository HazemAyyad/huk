<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhotographerApplication extends Model
{
    protected $fillable = [
    'photographer_ad_id',
    'full_name',
    'personal_photo',
    'governorate',
    'national_id',
    'passport',
    'age',
    'camera_equipment',
    'phone_egypt',
    'phone_egypt_2',
    'whatsapp_palestine',
    'family_in_egypt',
    'family_in_gaza',
    'entry_reason',
    'entry_date',
    'address',
    'education',
    'work_in_gaza',
    'has_experience',
    'experience_details',
    'case_details',
    'status',
    'ip_address',
];
    public function photographerAd()
    {
        return $this->belongsTo(PhotographerAd::class);
    }

}
