<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyMember extends Model
{
    protected $fillable = [
        'telegram_user_id',
        'full_name',
        'national_id',
        'relation',
        'birth_date',
        'is_orphan',
    ];

    // المرافق ينتمي إلى رب أسرة
    public function telegramUser()
    {
        return $this->belongsTo(TelegramUser::class);
    }

    public function orphanChild()
    {
        return $this->hasOne(OrphanChild::class);
    }

}
