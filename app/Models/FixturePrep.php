<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FixturePrep extends Model
{
    protected $fillable = [
        'fixture_id',
        'welcomed_by', 'welcomed_at',
        'kits_ready', 'kits_prepared_by', 'kits_prepared_at',
        'lane_ready', 'lane_prepared_by', 'lane_prepared_at',
        'training_ready', 'training_prepared_by', 'training_prepared_at',
    ];

    protected function casts(): array
    {
        return [
            'welcomed_at' => 'datetime',
            'kits_ready' => 'boolean',
            'kits_prepared_at' => 'datetime',
            'lane_ready' => 'boolean',
            'lane_prepared_at' => 'datetime',
            'training_ready' => 'boolean',
            'training_prepared_at' => 'datetime',
        ];
    }

    public function fixture(): BelongsTo
    {
        return $this->belongsTo(Fixture::class);
    }

    public function welcomer(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'welcomed_by');
    }
}
