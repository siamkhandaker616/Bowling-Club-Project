<?php

namespace App\Services\Simulation;

use App\Models\ClubConfig;
use App\Models\Inventory;
use App\Models\InventoryPurchase;
use App\Models\Payment;
use App\Services\Payments\SslCommerzGateway;
use Illuminate\Support\Facades\DB;

class PurchaseBillService
{
    public function __construct(private InventoryService $inventoryService, private SslCommerzGateway $gateway)
    {
    }

    public function createPending(Inventory $inventory, int $quantity, ?int $requesterId): ?InventoryPurchase
    {
        if ($quantity <= 0) {
            return null;
        }

        $unitCost = (float) $inventory->cost_per_unit;

        return InventoryPurchase::create([
            'inventory_id' => $inventory->id,
            'item_name' => $inventory->name,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total' => round($quantity * $unitCost, 2),
            'status' => 'pending',
            'auto_approved' => false,
            'requested_by' => $requesterId,
        ]);
    }

    public function autoApprove(Inventory $inventory, int $quantity, int $approverId): ?InventoryPurchase
    {
        if ($quantity <= 0) {
            return null;
        }

        $unitCost = (float) $inventory->cost_per_unit;
        $total = round($quantity * $unitCost, 2);

        return DB::transaction(function () use ($inventory, $quantity, $unitCost, $total, $approverId) {
            $bill = InventoryPurchase::create([
                'inventory_id' => $inventory->id,
                'item_name' => $inventory->name,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total' => $total,
                'status' => 'approved',
                'auto_approved' => true,
                'reviewed_by' => $approverId,
                'reviewed_at' => now(),
            ]);

            $this->chargeExpenses($total);

            $payment = Payment::create([
                'payable_type' => InventoryPurchase::class,
                'payable_id' => $bill->id,
                'transaction_id' => $this->gateway->generateTransactionId(),
                'amount' => $total,
                'currency' => 'BDT',
                'status' => 'success',
                'paid_at' => now(),
            ]);

            $bill->update(['payment_id' => $payment->id]);

            return $bill;
        });
    }

    public function reject(InventoryPurchase $bill, int $managerId): array
    {
        return DB::transaction(function () use ($bill, $managerId) {
            $locked = InventoryPurchase::whereKey($bill->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'pending') {
                abort(409, 'This bill has already been reviewed.');
            }

            $inventory = $locked->inventory;
            $returnQty = min($locked->quantity, $inventory ? $inventory->quantity : 0);
            $consumed = $locked->quantity - $returnQty;
            $fine = $consumed > 0 ? round($consumed * (float) $locked->unit_cost, 2) : 0.0;

            if ($returnQty > 0 && $inventory) {
                $this->inventoryService->adjust($inventory, -$returnQty, 'usage', 'Purchased stock returned — bill rejected', $managerId);
            }

            if ($fine > 0) {
                $this->chargeExpenses($fine);
            }

            $locked->update([
                'status' => 'rejected',
                'reviewed_by' => $managerId,
                'reviewed_at' => now(),
                'fine_amount' => $fine > 0 ? $fine : null,
            ]);

            return ['returned' => $returnQty, 'consumed' => $consumed, 'fine' => $fine];
        });
    }

    public function settlePayment(Payment $payment, array $response = [], ?int $reviewerId = null): bool
    {
        return DB::transaction(function () use ($payment, $response, $reviewerId) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->first();

            if (! $locked || $locked->status !== 'processing') {
                return false;
            }

            $bill = InventoryPurchase::whereKey($locked->payable_id)->lockForUpdate()->first();

            if (! $bill || $bill->status !== 'pending') {
                return false;
            }

            $this->chargeExpenses((float) $bill->total);

            $bill->update([
                'status' => 'approved',
                'reviewed_by' => $reviewerId ?? $bill->reviewed_by,
                'reviewed_at' => now(),
                'payment_id' => $locked->id,
            ]);

            $locked->markSuccessful($locked->transaction_id, $response);

            return true;
        });
    }

    public function chargeExpenses(float $amount): void
    {
        $cfg = ClubConfig::singleton();
        $cfg->total_expenses = $cfg->total_expenses + $amount;
        $cfg->save();
    }
}
