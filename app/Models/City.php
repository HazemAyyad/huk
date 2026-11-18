<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use \App\Traits\AssociationTombstones;

    protected $guarded = [];

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }
}
