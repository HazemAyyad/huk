<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaidSalary extends Model
{
    use \App\Traits\AssociationTombstones;


    protected $guarded = [];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
