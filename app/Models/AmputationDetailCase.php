<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmputationDetailCase extends Model
{
    protected $fillable = [
        'telegram_user_id',
        'full_name',
        'national_id',
        'birth_date',
        'age',
        'gender',
        'housing_status',
        'marital_status',
        'entry_date',
        'entry_method',
        'amputation_date',
        'amputation_type',
        'injury_reason',
        'needs_prosthetic',
        'received_help',
        'help_type',
        'health_description',
        'has_income',
        'latitude',
        'longitude',
        'person_image',
        'amputation_image',
        'guardian_id',
        'guardian_passport',
        'entry_stamp',
        'medical_reports',
        'rent_contract',
        'status',
        'rent_value',
        'needs_companion',
        'companion_full_name',
        'companion_national_id',
        'companion_birth_date',
        'companion_relationship',

        'reviewed_by',
        'reviewed_at',
        'admin_note',
    ];

    public function telegramUser()
    {
        return $this->belongsTo(TelegramUser::class);
    }
    public function reviewer()
{
    return $this->belongsTo(User::class, 'reviewed_by');
}
}