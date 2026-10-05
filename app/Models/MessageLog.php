<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Blameable; // استدعاء الـ Trait

class MessageLog extends Model
{
    use Blameable; 
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'telegram_user_id',
        'event_id',
        'sent_by',
        'sender_type',
        'message',
        'message_type',
        'delivery_status',
        'sent_at',
        'deleted_by',
        'deleted_at',
        'attended_at',
        'excused_at',
        'attended_by',
        'excused_by',
        'is_override',
        'override_by',
    ];

    public function user()
    {
        return $this->belongsTo(TelegramUser::class, 'telegram_user_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    // من أرسل الرسالة
    public function creator()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    // من قام بالتعديل (لو استخدمنا override)
    public function updater()
    {
        return $this->belongsTo(User::class, 'override_by');
    }
    
    // من قام بتسجيل الحضور
    public function attendedBy()
    {
        return $this->belongsTo(User::class, 'attended_by');
    }
    
    // من قام بتسجيل الاعتذار
    public function excusedBy()
    {
        return $this->belongsTo(User::class, 'excused_by');
    }
}