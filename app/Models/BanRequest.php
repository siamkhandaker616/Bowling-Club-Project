<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BanRequest extends Model
{
    protected $fillable = [
        'visitor_id', 'requested_by_staff_id', 'reason', 'evidence',
        'status', 'reviewed_by_admin_id', 'reviewed_at', 'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'requested_by_staff_id');
    }
}
