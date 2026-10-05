<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Blameable;

class Event extends Model
{
    use Blameable;

    protected $fillable = [
        'name',
        'event_date',
        'event_time',
        'aid_type',
        'value',
        'value_type',
        'value_text',
        'extras',
        'location',
        'invitation_message',
        'is_archived',
        'is_fixed',
        'default_message',
        'without_notification',
    ];

    public function messages()
    {
        return $this->hasMany(MessageLog::class);
    }
    
    public function assignments()
    {
        return $this->hasMany(TaskAssignment::class);
    }
    
    // من أنشأ
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // من عدل
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // من حذف
    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}