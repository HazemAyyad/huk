<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class RamadanDistribution extends Model
{
    protected $fillable = [
        'beneficiary_id',
        'distribution_date',
        'meals_taken',
        'created_by',
    ];

    public function beneficiary()
    {
        return $this->belongsTo(RamadanBeneficiary::class);
    }


    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

