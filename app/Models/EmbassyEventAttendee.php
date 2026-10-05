<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class EmbassyEventAttendee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'code',
        'full_name',
        'national_id',
        'notes',
        'attended',
    ];

    // **تلقائي ضبط المستخدمين عند الإضافة والتعديل والحذف**
    protected static function booted()
    {
        static::creating(function ($model) {
            if (Auth::check()) $model->created_by = Auth::id();
        });

        static::updating(function ($model) {
            if (Auth::check()) $model->updated_by = Auth::id();
        });

        static::deleting(function ($model) {
            if (Auth::check()) $model->deleted_by = Auth::id();
            $model->save(); // مهم لحفظ deleted_by قبل الحذف
        });
    }

    // علاقات للمستخدمين
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function editor() { return $this->belongsTo(User::class, 'updated_by'); }
    public function deleter() { return $this->belongsTo(User::class, 'deleted_by'); }
    
}