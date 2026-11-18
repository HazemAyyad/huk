<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InjuredCaseMessage extends Model
{
    use \App\Traits\AssociationTombstones;

    protected $guarded = [];
}
