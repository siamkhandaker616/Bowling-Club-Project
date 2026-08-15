<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visitor extends Model
{
    protected $fillable = [
        'user_id', 'name', 'email', 'phone', 'tier',
        'reputation_score', 'is_banned', 'ban_reason',
        'banned_by_admin_id', 'banned_at',
    ];

    protected function casts(): array
    {
        return [
            'reputation_score' => 'integer',
            'is_banned' => 'boolean',
            'banned_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(LaneBooking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(VisitorReview::class);
    }
}
