<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmputationMessage extends Model
{
    use \App\Traits\AssociationTombstones;

    protected $guarded = [];
}
