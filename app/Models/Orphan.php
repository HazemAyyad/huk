<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orphan extends Model
{
    protected $guarded = [];

    protected $dates = ['birth_date'];

    public function guardian()
    {
        return $this->belongsTo(Guardian::class);
    }
}
