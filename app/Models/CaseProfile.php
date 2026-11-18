<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseProfile extends Model
{
    use \App\Traits\AssociationTombstones;

    protected $fillable = [
        'key',
        'name',
        'is_active',
    ];

    public function categories()
    {
        return $this->hasMany(Category::class);
    }
}
