<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMember extends Model
{
    protected $fillable = ['team_id', 'name', 'average_score'];

    protected function casts(): array
    {
        return ['average_score' => 'float'];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
