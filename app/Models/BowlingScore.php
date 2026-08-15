<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BowlingScore extends Model
{
    protected $fillable = [
        'visitor_id', 'score', 'frames_data', 'played_at', 'is_high_score',
    ];

    protected function casts(): array
    {
        return [
            'frames_data' => 'array',
            'played_at' => 'datetime',
            'is_high_score' => 'boolean',
        ];
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }
}
