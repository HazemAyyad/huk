<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrphanCase extends Model
{
    protected $fillable = [
        'telegram_user_id',
        'orphan_status',
        'death_certificate',

        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    public function telegramUser()
    {
        return $this->belongsTo(TelegramUser::class);
    }
}