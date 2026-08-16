<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffMessage extends Model
{
    protected $fillable = [
        'staff_id', 'recipient_staff_id', 'confrontation_id', 'bubble_type', 'kind', 'body', 'date', 'read_at',
    ];

    public function confrontation(): BelongsTo
    {
        return $this->belongsTo(Confrontation::class);
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'read_at' => 'datetime',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'recipient_staff_id');
    }
}
