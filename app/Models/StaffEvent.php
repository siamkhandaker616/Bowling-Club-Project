<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffEvent extends Model
{
    protected $fillable = [
        'staff_id', 'event_type', 'severity', 'description', 'date', 'happiness_change',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'happiness_change' => 'integer',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
