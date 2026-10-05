<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BreadwinnerFamilyMember extends Model
{
    protected $guarded = [];

    public function family()
    {
        return $this->belongsTo(FamilyBreadwinner::class);
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
