<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryPurchase extends Model
{
    protected $fillable = [
        'inventory_id',
        'item_name',
        'quantity',
        'unit_cost',
        'total',
        'status',
        'auto_approved',
        'requested_by',
        'reviewed_by',
        'reviewed_at',
        'payment_id',
        'fine_amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'float',
        'total' => 'float',
        'auto_approved' => 'boolean',
        'reviewed_at' => 'datetime',
        'fine_amount' => 'float',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'requested_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'reviewed_by');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
