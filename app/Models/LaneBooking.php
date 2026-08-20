<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class LaneBooking extends Model
{
    protected $fillable = [
        'visitor_id', 'lane_id', 'date', 'time_slot', 'status',
        'queue_position', 'amount', 'compensation_claimed', 'compensation_type',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'queue_position' => 'integer',
            'amount' => 'decimal:2',
            'compensation_claimed' => 'boolean',
        ];
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function lane(): BelongsTo
    {
        return $this->belongsTo(Lane::class);
    }

    public function queueEntries(): HasMany
    {
        return $this->hasMany(BookingQueue::class, 'booking_id');
    }

    public function visitorReview(): HasOne
    {
        return $this->hasOne(VisitorReview::class, 'booking_id');
    }

    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable');
    }
}
