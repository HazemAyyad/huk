<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class StrandedFamilyMember extends Model
{
    protected $guarded = [];

    public function family()
    {
        return $this->belongsTo(StrandedFamily::class, 'stranded_family_id');
    }

    // حساب العمر تلقائياً
    protected static function booted()
    {
        static::saving(function ($member) {
            if ($member->birth_date) {
                $member->age = Carbon::parse($member->birth_date)->age;
            }
        });
    }
}
