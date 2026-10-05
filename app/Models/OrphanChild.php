<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrphanChild extends Model
{
    protected $fillable = [
        'family_member_id',
        'birth_date',
        'age',
        'gender',
        'personal_photo',
        'lives_with_guardian',
        'education_level',
        'study_type',
        'has_chronic_disease',
        'has_disability',
        'passport',
        'entry_stamp',
        'birth_certificate',
    ];

    public function familyMember()
    {
        return $this->belongsTo(FamilyMember::class);
    }
}
