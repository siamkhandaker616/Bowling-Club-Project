<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Confrontation extends Model
{
    protected $fillable = [
        'reporter_staff_id', 'accused_staff_id', 'incident_type',
        'incident_description', 'db_verified', 'staff_response',
        'investigation_result', 'manager_verdict', 'date', 'happiness_impacts',
    ];

    protected function casts(): array
    {
        return [
            'db_verified' => 'boolean',
            'date' => 'date',
            'happiness_impacts' => 'array',
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
}
