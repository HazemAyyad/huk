<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuardianMessage extends Model
{
    use \App\Traits\AssociationTombstones;

    protected $guarded = [];
}
