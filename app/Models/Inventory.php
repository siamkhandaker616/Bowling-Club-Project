<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    protected $fillable = [
        'name', 'category', 'quantity', 'max_quantity',
        'condition', 'reorder_threshold', 'cost_per_unit', 'last_restocked_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'max_quantity' => 'integer',
            'reorder_threshold' => 'integer',
            'cost_per_unit' => 'decimal:2',
            'last_restocked_at' => 'datetime',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(InventoryEvent::class);
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->reorder_threshold;
    }
}
