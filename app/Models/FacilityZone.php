<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacilityZone extends Model
{
    protected $fillable = [
        'club_id', 'name', 'description', 'open_time', 'close_time',
        'facilities', 'map_key', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'facilities' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
