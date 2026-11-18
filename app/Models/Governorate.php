<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Governorate extends Model
{
    use \App\Traits\AssociationTombstones;


    protected $guarded = [];

    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
