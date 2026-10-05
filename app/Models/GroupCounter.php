<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupCounter extends Model
{
    protected $fillable = [
        'group_key',
        'counter'
    ];
}
