<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TelegramUser extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'category_id',
        'access_code',
        'chat_id',
        'business_connection_id',
        'source',
        'username',
        'first_name',
        'replied_once',
        'step',
        'full_name',
        'birth_date',
        'entry_reason',
        'national_id',
        'benefit_count',
        'whatsapp',
        'telegram_number',
        'egypt_number',
        'family_members',
        'governorate',
        'city',
        'address',
        'benefited_before',
        'target_case',
        'amputation_type',
        'disease_type',
        'companions', // سنستغني عنه لاحقًا
        'is_completed',
        'after_completed_messages_count',
        'is_blocked',
        'blocked_reason',
        'spam_count',
        'last_message_at',
        'is_processing',
        'current_family_member_index',
        'last_family_member_name',
        'created_by',
        'updated_by',
        'deleted_by',
        'is_approved',
        'entry_to_egypt',
        'rent_amount',
        'landlord_phone',
        'approved_by',
        'approval_description',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'user_categories', 'telegram_user_id', 'category_id')
            ->withTimestamps();
    }
    public function lastMessage()
    {
        return $this->hasOne(MessageLog::class, 'telegram_user_id')
                    ->with('event')
                    ->latestOfMany();
    }
    // رب الأسرة لديه عدة مرافقين
    public function familyMembers()
    {
        return $this->hasMany(FamilyMember::class);
    }

    public function messages()
    {
        return $this->hasMany(MessageLog::class);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'telegram_user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function guardianDetail()
    {
        return $this->hasOne(GuardianDetail::class);
    }

    public function orphanCase()
    {
        return $this->hasOne(OrphanCase::class);
    }

    public function amputationDetailCase()
    {
        return $this->hasOne(AmputationDetailCase::class);
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
    
    public function notes()
    {
        return $this->hasMany(TelegramUserNote::class);
    }
}


