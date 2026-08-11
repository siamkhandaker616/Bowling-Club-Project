<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryEvent extends Model
{
    protected $fillable = [
        'inventory_id', 'staff_id', 'event_type', 'quantity_change',
        'description', 'date',
    ];

    protected function casts(): array
    {
        return [
            'quantity_change' => 'integer',
            'date' => 'date',
        ];
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
