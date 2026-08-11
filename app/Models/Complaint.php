<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    protected $fillable = [
        'visitor_id', 'staff_id', 'raised_by_staff_id', 'type',
        'description', 'status', 'resolution', 'compensation_type',
        'resolved_by', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function raisedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'raised_by_staff_id');
    }
}
