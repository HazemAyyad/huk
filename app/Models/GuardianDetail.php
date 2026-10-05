<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuardianDetail extends Model
{
    protected $fillable = [
        'telegram_user_id',
        'birth_date',
        'age',
        'relationship_to_case',
        'gender',
        'housing_status',
        'monthly_income',
        'latitude',
        'longitude',
        'personal_photo',
        'id_document',
        'passport',
        'entry_stamp',
        'medical_reports',
        'rent_contract',
    ];

    public function telegramUser()
    {
        return $this->belongsTo(TelegramUser::class);
    }
}
