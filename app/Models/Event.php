<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Event extends Model
{
    protected $fillable = [
        'title', 'description', 'date', 'time',
        'venue', 'max_capacity', 'current_rsvps', 'price',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'time' => 'date:H:i',
            'max_capacity' => 'integer',
            'current_rsvps' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(Rsvp::class);
    }

    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable');
    }

    public function isFull(): bool
    {
        return $this->current_rsvps >= $this->max_capacity;
    }

    public function remainingSpots(): int
    {
        return max(0, $this->max_capacity - $this->current_rsvps);
    }
}
