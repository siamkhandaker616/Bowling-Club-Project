<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffReview extends Model
{
    protected $fillable = [
        'staff_id', 'visitor_id', 'booking_id', 'rating',
        'body', 'was_polite', 'caused_issues',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'was_polite' => 'boolean',
            'caused_issues' => 'boolean',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(LaneBooking::class);
    }
}
