<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeatRegistration extends Model
{
    protected $fillable = [
        'telegram_user_id',
        'national_id',
        'want_tripe',
        'want_legs',
        'separate_family',
        'notes',
    ];
    
    public function telegramUser()
    {
        return $this->belongsTo(TelegramUser::class, 'telegram_user_id');
    }
}