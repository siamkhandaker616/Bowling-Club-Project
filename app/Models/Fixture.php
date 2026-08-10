<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fixture extends Model
{
    protected $fillable = [
        'home_team_id', 'away_team_id', 'date', 'time',
        'lane_id', 'league_id', 'home_score', 'away_score', 'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'time' => 'date:H:i',
            'home_score' => 'integer',
            'away_score' => 'integer',
        ];
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function lane(): BelongsTo
    {
        return $this->belongsTo(Lane::class);
    }
}
