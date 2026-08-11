<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LaneBooking extends Model
{
    protected $fillable = [
        'visitor_id', 'lane_id', 'date', 'time_slot', 'status',
        'queue_position', 'compensation_claimed', 'compensation_type',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'queue_position' => 'integer',
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
        return $this->hasMany(BookingQueue::class);
    }

    public function visitorReview(): HasOne
    {
        return $this->hasOne(VisitorReview::class);
    }
}
