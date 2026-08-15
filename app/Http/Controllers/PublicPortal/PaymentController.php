<?php

namespace App\Http\Controllers\PublicPortal;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Payment;
use App\Models\ProductOrder;
use App\Models\Rsvp;
use App\Services\Payments\PaymentSettler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private PaymentSettler $settler)
    {
    }

    public function success(Request $request, Payment $payment): View
    {
        if (! $payment->isSuccessful() && $payment->status === 'processing') {
            $this->settler->settleIfPossible($payment);
        }

        if ($payment->isSuccessful() && $payment->payable instanceof ProductOrder) {
            CartItem::where('session_id', $request->session()->getId())->delete();
        }

        return view('portal.payments.result', ['payment' => $payment, 'status' => 'success']);
    }

    public function fail(Payment $payment): View
    {
        $payment->update(['status' => 'failed']);
        $this->voidRsvp($payment);

        return view('portal.payments.result', ['payment' => $payment, 'status' => 'fail']);
    }

    public function cancel(Payment $payment): View
    {
        $payment->update(['status' => 'cancelled']);
        $this->voidRsvp($payment);

        return view('portal.payments.result', ['payment' => $payment, 'status' => 'cancel']);
    }

    public function ipn(Request $request): JsonResponse
    {
        $tranId = (string) $request->input('tran_id');

        $payment = Payment::where('transaction_id', $tranId)->first();

        if (! $payment) {
            return response()->json(['status' => 'FAILED']);
        }

        if ($payment->isSuccessful()) {
            return response()->json(['status' => 'VALID']);
        }

        if ($this->settler->settleIfPossible($payment)) {
            return response()->json(['status' => 'VALID']);
        }

        return response()->json(['status' => 'FAILED']);
    }

    private function voidRsvp(Payment $payment): void
    {
        $payable = $payment->payable;

        if ($payable instanceof Rsvp && ! $payable->isConfirmed()) {
            $payable->update(['status' => 'cancelled']);
        }
    }
}
