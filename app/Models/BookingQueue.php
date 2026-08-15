<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingQueue extends Model
{
    protected $fillable = [
        'booking_id', 'visitor_id', 'lane_id', 'date',
        'time_slot', 'position', 'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'position' => 'integer',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(LaneBooking::class);
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function lane(): BelongsTo
    {
        return $this->belongsTo(Lane::class);
    }
}
