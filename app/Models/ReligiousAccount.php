<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

final class ReligiousAccount extends Authenticatable
{
    use HasApiTokens;
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'username'];

    protected $hidden = ['password', 'google_subject'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'quran_revision' => 'integer', 'password_enabled' => 'boolean'];
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ReligiousPasswordReset($token));
    }
}
