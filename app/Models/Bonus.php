<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bonus extends Model
{
    protected $fillable = [
        'staff_id', 'type', 'reason', 'amount_or_hours', 'date', 'issued_by',
    ];

    protected function casts(): array
    {
        return [
            'amount_or_hours' => 'decimal:2',
            'date' => 'date',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'issued_by');
    }
}
