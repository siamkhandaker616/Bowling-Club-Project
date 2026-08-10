<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TouringRequest extends Model
{
    protected $fillable = [
        'team_name', 'home_club', 'arrival_date',
        'player_count', 'message', 'status',
    ];

    protected function casts(): array
    {
        return [
            'arrival_date' => 'date',
            'player_count' => 'integer',
            'status' => 'string',
        ];
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
