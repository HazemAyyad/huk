<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deceased extends Model
{
    use \App\Traits\AssociationTombstones;


    protected $guarded = [];

    public function guardian()
    {
        return $this->belongsTo(Guardian::class);
    }
}
