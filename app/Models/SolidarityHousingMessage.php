<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolidarityHousingMessage extends Model
{
    use \App\Traits\AssociationTombstones;

    protected $guarded = [];
}
