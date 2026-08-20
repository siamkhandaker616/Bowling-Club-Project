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
                'customer_email' => $request->user()->email,
            ]);
            $purchase->update(['reviewed_by' => $request->user()->staff?->id]);
        }

        if (! $this->gateway->isConfigured()) {
            $payment->update(['status' => 'processing']);
            $this->service->settlePayment($payment->refresh(), [], $request->user()->staff?->id);

            session()->flash('success', $purchase->item_name . ' bill approved — ৳' . number_format((float) $purchase->total, 2) . ' charged to club expenses (simulated payment).');

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

        return response()->json([
            'gateway_url' => $response['GatewayPageURL'],
            'payment_id' => $payment->id,
        ]);
    }

    public function pay(Request $request, InventoryPurchase $purchase)
    {
        if ($purchase->status !== 'approved' || $purchase->payment?->isSuccessful()) {
            session()->flash('success', $purchase->item_name . ' bill is already paid.');

            return redirect()->route('manager.inventory.purchases.index');
        }

        $payment = Payment::where('payable_type', InventoryPurchase::class)
            ->where('payable_id', $purchase->id)
            ->where('status', '!=', 'success')
            ->first();

        if (! $payment) {
            $payment = Payment::create([
                'payable_type' => InventoryPurchase::class,
                'payable_id' => $purchase->id,
                'transaction_id' => $this->gateway->generateTransactionId(),
                'amount' => (float) $purchase->total,
                'currency' => 'BDT',
                'status' => 'pending',
                'customer_name' => $request->user()->name,
                'customer_email' => $request->user()->email,
            ]);
        }

        if (! $this->gateway->isConfigured()) {
            $payment->update(['status' => 'processing']);
            $this->service->settlePayment($payment->refresh(), [], $request->user()->staff?->id);

            session()->flash('success', $purchase->item_name . ' bill paid — ৳' . number_format((float) $purchase->total, 2) . ' charged to club expenses (simulated payment).');

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
            Log::warning('SSLCommerz session failed for inventory purchase payment: ' . $e->getMessage());
            $payment->update(['status' => 'failed', 'error_message' => 'Payment service unreachable.']);

            session()->flash('error', 'Couldn\'t reach the payment service — try again in a moment.');

            return redirect()->route('manager.inventory.purchases.index');
        }

        if (($response['status'] ?? '') !== 'SUCCESS') {
            $payment->update(['status' => 'failed', 'error_message' => $response['failedreason'] ?? 'Payment session declined.']);

            session()->flash('error', 'The payment gateway declined the session.');

            return redirect()->route('manager.inventory.purchases.index');
        }

        $payment->update(['status' => 'processing', 'session_key' => $response['sessionkey'] ?? null]);

        return response()->json([
            'gateway_url' => $response['GatewayPageURL'],
            'payment_id' => $payment->id,
        ]);
    }

    public function status(Payment $payment)
    {
        $payment->refresh();

        return response()->json([
            'status' => $payment->status,
            'successful' => $payment->isSuccessful(),
        ]);
    }

    public function reject(Request $request, InventoryPurchase $purchase)
    {
        $result = $this->service->reject($purchase, $request->user()->staff?->id);

        if ($result['fine'] > 0) {
            session()->flash('flash', [
                'type' => 'error',
                'message' => $purchase->item_name . ' bill rejected — ' . $result['returned'] . ' units returned, ' . $result['consumed'] . ' already used. ৳' . number_format($result['fine'], 2) . ' fine charged to club expenses.',
            ]);
        } else {
            session()->flash('success', $purchase->item_name . ' bill rejected — ' . $result['returned'] . ' units returned to stock.');
        }

        return redirect()->route('manager.inventory.purchases.index');
    }

    public function success(Request $request, Payment $payment)
    {
        if (! $payment->isSuccessful() && $payment->status === 'processing') {
            $postStatus = strtolower((string) $request->input('status', ''));

            if (in_array($postStatus, ['valid', 'success'], true)) {
                $payload = $request->only([
                    'tran_id', 'amount', 'card_type', 'card_no', 'bank_tran_id',
                    'status', 'tran_date', 'currency', 'store_amount',
                ]);

                $this->service->settlePayment($payment, $payload);
                $payment->refresh();
            } elseif ($payment->session_key) {
                try {
                    $validation = $this->gateway->validate($payment->session_key, (string) $payment->transaction_id);

                    if (($validation['status'] ?? '') === 'VALID') {
                        $this->service->settlePayment($payment, $validation);
                        $payment->refresh();
                    }
                } catch (\Throwable $e) {
                    Log::warning('SSLCommerz validation failed on callback: ' . $e->getMessage());
                }
            }
        }

        return response()->view('sim.manager.inventory.payment-callback', [
            'payment' => $payment,
            'status' => $payment->isSuccessful() ? 'success' : 'pending',
        ]);
    }

    public function fail(Payment $payment)
    {
        if (! $payment->isSuccessful() && $payment->status !== 'cancelled') {
            $payment->update(['status' => 'failed', 'error_message' => 'Payment failed or was declined.']);
        }

        return response()->view('sim.manager.inventory.payment-callback', [
            'payment' => $payment,
            'status' => 'failed',
        ]);
    }

    public function cancel(Payment $payment)
    {
        if (! $payment->isSuccessful()) {
            $payment->update(['status' => 'cancelled', 'error_message' => 'Payment cancelled before completion.']);
        }

        return response()->view('sim.manager.inventory.payment-callback', [
            'payment' => $payment,
            'status' => 'cancelled',
        ]);
    }

    public function ipn(Request $request)
    {
        $tranId = (string) $request->input('tran_id');
        $payment = Payment::where('transaction_id', $tranId)->first();

        if (! $payment || ! $payment->payable instanceof InventoryPurchase) {
            return response()->json(['status' => 'FAILED']);
        }

        if ($payment->isSuccessful()) {
            return response()->json(['status' => 'VALID']);
        }

        $postStatus = strtolower((string) $request->input('status', ''));

        if (in_array($postStatus, ['valid', 'success'], true) && $payment->status === 'processing') {
            $payload = $request->only([
                'tran_id', 'amount', 'card_type', 'card_no', 'bank_tran_id',
                'status', 'tran_date', 'currency', 'store_amount',
            ]);

            if ($this->service->settlePayment($payment, $payload)) {
                return response()->json(['status' => 'VALID']);
            }
        }

        if ($payment->status === 'processing' && $payment->session_key) {
            try {
                $validation = $this->gateway->validate($payment->session_key, $tranId);

                if (($validation['status'] ?? '') === 'VALID' && $this->service->settlePayment($payment, $validation)) {
                    return response()->json(['status' => 'VALID']);
                }
            } catch (\Throwable $e) {
                Log::warning('SSLCommerz IPN validation failed: ' . $e->getMessage());
            }
        }

        return response()->json(['status' => 'FAILED']);
    }
}
