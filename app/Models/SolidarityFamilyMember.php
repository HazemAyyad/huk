<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolidarityFamilyMember extends Model
{
    use \App\Traits\AssociationTombstones;

    protected $fillable = [
        'solidarity_housing_id',
        'name',
        'national_id',
        'passport_number',
        'birth_date',
        'age',
        'relation',
        'education_level',
        'health_status',
        'disease_type',
        'has_disability',
        'disability_type',
        'disability_description'
    ];

    protected $casts = [
        'has_disability' => 'boolean',
        'birth_date' => 'date',
    ];

    public function case()
    {
        return $this->belongsTo(SolidarityHousing::class, 'solidarity_housing_id');
    }
}
