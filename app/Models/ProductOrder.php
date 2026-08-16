<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class ProductOrder extends Model
{
    protected $fillable = [];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable');
    }

    public function total(): float
    {
        return (float) $this->items->sum(fn (OrderItem $item) => (float) $item->unit_price * $item->quantity);
    }

    public function isSuccessful(): bool
    {
        return $this->payment?->isSuccessful() ?? false;
    }

    public function fulfill(): void
    {
        foreach ($this->items as $item) {
            $product = Product::whereKey($item->product_id)->lockForUpdate()->first();

            if ($product) {
                $product->update(['stock' => max(0, (int) $product->stock - (int) $item->quantity)]);
            }
        }
    }
}
