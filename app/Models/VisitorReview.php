<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorReview extends Model
{
    protected $fillable = [
        'visitor_id', 'booking_id', 'rating', 'body',
        'helpful_count', 'not_helpful_count',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'helpful_count' => 'integer',
            'not_helpful_count' => 'integer',
        ];
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
