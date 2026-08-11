<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffRelationship extends Model
{
    protected $fillable = [
        'staff_a_id', 'staff_b_id', 'level', 'score',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
        ];
    }

    public function staffA(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_a_id');
    }

    public function staffB(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_b_id');
    }
}
