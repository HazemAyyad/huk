<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyBreadwinnerMessage extends Model
{
    use \App\Traits\AssociationTombstones;

    protected $guarded = [];
}
