<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use \App\Traits\AssociationTombstones;


    protected $guarded = [];

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
