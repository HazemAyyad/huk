<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use \App\Traits\AssociationTombstones;

    protected $guarded = [];

    
}
