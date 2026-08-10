<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'phone',
        'is_npc',
        'is_active',
        'google_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_npc' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSteward(): bool
    {
        return $this->role === 'steward';
    }

    public function isCaretaker(): bool
    {
        return $this->role === 'caretaker';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }
}
