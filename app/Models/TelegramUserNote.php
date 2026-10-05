<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TelegramUserNote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'telegram_user_id',
        'note',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function telegramUser()
    {
        return $this->belongsTo(TelegramUser::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class,'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class,'deleted_by');
    }
}

