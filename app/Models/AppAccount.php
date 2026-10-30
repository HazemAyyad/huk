<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/** Login aggregation only. Passwords remain in their existing owning providers. */
final class AppAccount extends Authenticatable
{
    use HasApiTokens;

    protected $guarded = ['id'];

    protected $attributes = ['active' => true, 'association_enabled' => false, 'religious_enabled' => false];

    protected function casts(): array
    {
        return ['association_enabled' => 'boolean', 'religious_enabled' => 'boolean', 'active' => 'boolean'];
    }

    public function areas(): array
    {
        return array_values(array_filter([
            $this->association_enabled && $this->user_id ? 'association' : null,
            $this->religious_enabled && $this->religious_account_id ? 'religious' : null,
        ]));
    }
}
