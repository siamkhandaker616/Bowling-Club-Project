<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Accident extends Model
{
    protected $fillable = [
        'staff_id', 'shift_id', 'type', 'severity', 'description',
        'resolved', 'resolution', 'affected_booking_id',
    ];

    protected function casts(): array
    {
        return [
            'resolved' => 'boolean',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function affectedBooking(): BelongsTo
    {
        return $this->belongsTo(LaneBooking::class, 'affected_booking_id');
    }
}
