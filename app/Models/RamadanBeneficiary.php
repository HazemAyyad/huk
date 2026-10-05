<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class RamadanBeneficiary extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'full_name',
        'national_id',
        'code',
        'family_members_count',
        'daily_meals',
        'created_by',
        'updated_by',
        'deleted_by'
    ];
    public function distributions()
    {
        return $this->hasMany(RamadanDistribution::class, 'beneficiary_id');
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
