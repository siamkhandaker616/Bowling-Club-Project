<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SnitchReport extends Model
{
    protected $fillable = [
        'reporter_staff_id', 'accused_staff_id', 'quote', 'status',
        'confrontation_id', 'steward_note', 'escalated_at', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'escalated_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'reporter_staff_id');
    }

    public function accused(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'accused_staff_id');
    }

    public function confrontation(): BelongsTo
    {
        return $this->belongsTo(Confrontation::class);
    }
}
