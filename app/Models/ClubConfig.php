<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubConfig extends Model
{
    protected $fillable = [
        'bad_day_mode', 'current_day', 'reputation',
        'total_revenue', 'total_expenses', 'last_advanced_at',
    ];

    protected function casts(): array
    {
        return [
            'bad_day_mode' => 'boolean',
            'current_day' => 'integer',
            'reputation' => 'integer',
            'total_revenue' => 'decimal:2',
            'total_expenses' => 'decimal:2',
            'last_advanced_at' => 'datetime',
        ];
    }

    public static function singleton(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
