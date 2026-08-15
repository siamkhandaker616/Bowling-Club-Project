<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = [
        'staff_id', 'date', 'time_slot', 'lane_id', 'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'lane_id' => 'integer',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function lane(): BelongsTo
    {
        return $this->belongsTo(Lane::class);
    }

    public function accidents(): HasMany
    {
        return $this->hasMany(Accident::class);
    }
}
