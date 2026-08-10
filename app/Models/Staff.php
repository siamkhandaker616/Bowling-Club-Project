<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    protected $fillable = [
        'user_id', 'role', 'portrait_happy', 'portrait_disappointed',
        'base_salary', 'current_salary', 'happiness', 'performance_score',
        'honesty_score', 'hire_date', 'is_active', 'warnings_count',
    ];

    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'current_salary' => 'decimal:2',
            'happiness' => 'integer',
            'performance_score' => 'integer',
            'honesty_score' => 'integer',
            'hire_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function personalities(): BelongsToMany
    {
        return $this->belongsToMany(Personality::class, 'staff_personalities');
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function accidents(): HasMany
    {
        return $this->hasMany(Accident::class);
    }

    public function penalties(): HasMany
    {
        return $this->hasMany(Penalty::class);
    }

    public function bonuses(): HasMany
    {
        return $this->hasMany(Bonus::class);
    }

    public function staffEvents(): HasMany
    {
        return $this->hasMany(StaffEvent::class);
    }
}
