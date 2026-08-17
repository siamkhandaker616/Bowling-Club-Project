<?php

namespace App\Http\Controllers\Sim\Manager;

use App\Http\Controllers\Controller;
use App\Models\InventoryPurchase;
use App\Models\Payment;
use App\Services\Payments\SslCommerzGateway;
use App\Services\Simulation\PurchaseBillService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InventoryPurchaseController extends Controller
{
    public function __construct(private PurchaseBillService $service, private SslCommerzGateway $gateway)
    {
    }

    public function index()
    {
        $bills = InventoryPurchase::with('inventory', 'requestedBy.user', 'reviewedBy.user', 'payment')
            ->latest()
            ->limit(50)
            ->get();

        $pendingBills = $bills->where('status', 'pending');
        $pendingTotal = round((float) $pendingBills->sum('total'), 2);

        return view('sim.manager.inventory.purchases', compact('bills', 'pendingBills', 'pendingTotal'));
    }

    public function accept(Request $request, InventoryPurchase $purchase)
    {
        if (! $purchase->isPending()) {
            abort(409, 'This bill has already been reviewed.');
        }

        $payment = Payment::where('payable_type', InventoryPurchase::class)
            ->where('payable_id', $purchase->id)
            ->first();

        if ($payment && $payment->isSuccessful()) {
            session()->flash('success', $purchase->item_name . ' bill is already approved and paid.');

            return redirect()->route('manager.inventory.purchases.index');
        }

        if (! $payment) {
            $payment = Payment::create([
                'payable_type' => InventoryPurchase::class,
                'payable_id' => $purchase->id,
                'transaction_id' => $this->gateway->generateTransactionId(),
                'amount' => (float) $purchase->total,
                'currency' => 'BDT',
                'status' => 'pending',
                'customer_name' => $request->user()->name,
            ]);
            $purchase->update(['reviewed_by' => $request->user()->staff?->id]);
        }

        if (! $this->gateway->isConfigured()) {
            $payment->update(['status' => 'processing']);
            $this->service->settlePayment($payment->refresh(), [], $request->user()->staff?->id);

            session()->flash('success', $purchase->item_name . ' bill approved — $' . number_format((float) $purchase->total, 2) . ' charged to club expenses (simulated payment).');

            return redirect()->route('manager.inventory.purchases.index');
        }

        try {
            $response = $this->gateway->initSession([
                'total_amount' => (string) $purchase->total,
                'currency' => 'BDT',
                'tran_id' => $payment->transaction_id,
                'success_url' => route('manager.inventory.purchases.success', $payment),
                'fail_url' => route('manager.inventory.purchases.fail', $payment),
                'cancel_url' => route('manager.inventory.purchases.cancel', $payment),
                'ipn_url' => route('sim.inventory.purchases.ipn'),
                'cus_name' => $request->user()->name,
                'cus_email' => $request->user()->email,
                'product_name' => 'Inventory purchase: ' . $purchase->item_name,
                'product_category' => 'Inventory & Supplies',
                'product_profile' => 'general',
            ]);
        } catch (\Throwable $e) {
            Log::warning('SSLCommerz session failed for inventory purchase: ' . $e->getMessage());
            $payment->update(['status' => 'failed', 'error_message' => 'Payment service unreachable.']);

            session()->flash('error', 'Couldn\'t reach the payment service — the bill is still pending. Try again in a moment.');

            return redirect()->route('manager.inventory.purchases.index');
        }

        if (($response['status'] ?? '') !== 'SUCCESS') {
            $payment->update(['status' => 'failed', 'error_message' => $response['failedreason'] ?? 'Payment session declined.']);

            session()->flash('error', 'The payment gateway declined the session — the bill is still pending.');

            return redirect()->route('manager.inventory.purchases.index');
        }

        $payment->update(['status' => 'processing', 'session_key' => $response['sessionkey'] ?? null]);

        return redirect()->away($response['GatewayPageURL']);
    }

    public function reject(Request $request, InventoryPurchase $purchase)
    {
        $result = $this->service->reject($purchase, $request->user()->staff?->id);

        if ($result['fine'] > 0) {
            session()->flash('flash', [
                'type' => 'error',
                'message' => $purchase->item_name . ' bill rejected — ' . $result['returned'] . ' units returned, ' . $result['consumed'] . ' already used. $' . number_format($result['fine'], 2) . ' fine charged to club expenses.',
            ]);
        } else {
            session()->flash('success', $purchase->item_name . ' bill rejected — ' . $result['returned'] . ' units returned to stock.');
        }

        return redirect()->route('manager.inventory.purchases.index');
    }

    public function success(Request $request, Payment $payment)
    {
        if (! $payment->isSuccessful() && $payment->status === 'processing' && $payment->session_key) {
            $validation = $this->gateway->validate($payment->session_key, (string) $payment->transaction_id);

            if (($validation['status'] ?? '') === 'VALID') {
                $this->service->settlePayment($payment, $validation, $request->user()->staff?->id);
                $payment->refresh();
            }
        }

        if ($payment->isSuccessful()) {
            session()->flash('success', 'Purchase paid — the club expenses have been updated.');
        } else {
            session()->flash('flash', ['type' => 'neutral', 'message' => 'Payment is still clearing — it will settle automatically. No action needed.']);
        }

        return redirect()->route('manager.inventory.purchases.index');
    }

    public function fail(Payment $payment)
    {
        if (! $payment->isSuccessful() && $payment->status !== 'cancelled') {
            $payment->update(['status' => 'failed', 'error_message' => 'Payment failed or was declined.']);
        }

        session()->flash('error', 'Payment didn\'t go through — the bill is still pending. You can accept it again.');

        return redirect()->route('manager.inventory.purchases.index');
    }

    public function cancel(Payment $payment)
    {
        if (! $payment->isSuccessful()) {
            $payment->update(['status' => 'cancelled', 'error_message' => 'Payment cancelled before completion.']);
        }

        session()->flash('flash', ['type' => 'neutral', 'message' => 'Payment cancelled — the bill is still pending. You can accept it again.']);

        return redirect()->route('manager.inventory.purchases.index');
    }

    public function ipn(Request $request)
    {
        $payment = Payment::where('transaction_id', (string) $request->input('tran_id'))->first();

        if (! $payment || ! $payment->payable instanceof InventoryPurchase) {
            return response()->json(['status' => 'FAILED']);
        }

        if ($payment->isSuccessful()) {
            return response()->json(['status' => 'VALID']);
        }

        if ($payment->status === 'processing' && $payment->session_key) {
            $validation = $this->gateway->validate($payment->session_key, (string) $payment->transaction_id);

            if (($validation['status'] ?? '') === 'VALID' && $this->service->settlePayment($payment, $validation)) {
                return response()->json(['status' => 'VALID']);
            }
        }

        return response()->json(['status' => 'FAILED']);
    }
}
