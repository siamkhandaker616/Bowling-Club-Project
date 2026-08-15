<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lane extends Model
{
    protected $fillable = [
        'lane_number', 'status', 'current_booking_id',
        'last_maintained_at', 'oil_level',
    ];

    protected function casts(): array
    {
        return [
            'last_maintained_at' => 'datetime',
            'oil_level' => 'integer',
        ];
    }
}
