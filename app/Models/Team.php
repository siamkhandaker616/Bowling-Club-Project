<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = ['name', 'league_id', 'wins', 'losses', 'draws'];

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function fixtures(): HasMany
    {
        return $this->hasMany(Fixture::class, 'home_team_id');
    }

    public function awayFixtures(): HasMany
    {
        return $this->hasMany(Fixture::class, 'away_team_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }
}
