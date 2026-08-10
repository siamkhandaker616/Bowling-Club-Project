<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Rsvp extends Model
{
    protected $fillable = [
        'event_id', 'visitor_name', 'visitor_email', 'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable');
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }
}
