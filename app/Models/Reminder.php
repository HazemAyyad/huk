<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = [
        'title',
        'notes',
        'day_of_month',
        'completed_for_month'
    ];
}
