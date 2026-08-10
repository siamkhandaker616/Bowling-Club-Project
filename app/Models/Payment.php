<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    protected $fillable = [
        'payable_type',
        'payable_id',
        'transaction_id',
        'session_key',
        'amount',
        'currency',
        'status',
        'customer_name',
        'customer_email',
        'customer_phone',
        'response',
        'error_message',
        'paid_at',
    ];

    protected $casts = [
        'response' => 'array',
        'paid_at' => 'datetime',
    ];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function markSuccessful(string $transactionId, array $response = []): void
    {
        $this->update([
            'status' => 'success',
            'transaction_id' => $transactionId ?: $this->transaction_id,
            'response' => $response,
            'paid_at' => now(),
        ]);
    }
}
