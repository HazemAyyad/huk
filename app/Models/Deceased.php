<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deceased extends Model
{

    protected $guarded = [];

    public function guardian()
    {
        return $this->belongsTo(Guardian::class);
    }
}
