<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskAssignment extends Model
{
    protected $fillable = [
        'event_id',        // الفاعلية المرتبطة
        'telegram_user_id',         // الشخص المكلف
        'role',            // الوظيفة أو المهمة (كاشير، كتابة بطاقات، تصوير، ... )
        'status',          // حالة المهمة (✅, 🟢, 🟡, ⛔)
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function telegramUser()
    {
        return $this->belongsTo(TelegramUser::class, 'telegram_user_id');
    }
}