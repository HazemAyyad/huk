<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StrandedFamilyMessage extends Model
{
    use \App\Traits\AssociationTombstones;

    protected $guarded = [];
}
