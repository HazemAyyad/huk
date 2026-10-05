<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Blameable;

class Category extends Model
{
    use Blameable;

    protected $fillable = [
        'case_profile_id',
        'name',
        'prefix',
        'last_number',
        'total_assigned',
        'is_active',
    ];

    public function users()
    {
        return $this->belongsToMany(TelegramUser::class, 'user_categories', 'category_id', 'telegram_user_id');
    }

    public function caseProfile()
    {
        return $this->belongsTo(CaseProfile::class);
    }

    public function telegramUsers()
    {
        return $this->hasMany(TelegramUser::class);
    }

    public function generateNextCode(): string
    {
        return $this->prefix . ($this->last_number + 1);
    }

    // من أنشأ التصنيف
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