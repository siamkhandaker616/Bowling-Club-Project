<?php

namespace App\Http\Controllers\PublicPortal;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ProductOrder;
use App\Models\Rsvp;
use App\Services\Payments\PaymentSettler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private PaymentSettler $settler)
    {
    }

    public function success(Request $request, Payment $payment): View
    {
        $sessionId = $payment->response['session_id'] ?? $request->session()->getId();

        if (! $payment->isSuccessful() && $payment->status === 'processing') {
            $this->settler->settleIfPossible($payment);
            $payment->refresh();
        }

        $payment->loadMorph('payable', [ProductOrder::class => ['items.product'], Rsvp::class => ['event']]);

        if ($payment->isSuccessful() && $payment->payable instanceof ProductOrder) {
            $this->settler->clearCartFor($payment, $sessionId);
        }

        return view('portal.payments.result', [
            'payment' => $payment,
            'status' => 'success',
            'payload' => $this->resultPayload($payment, 'success'),
        ]);
    }

    public function fail(Payment $payment): View
    {
        if (! $payment->isSuccessful()) {
            $payment->update(['status' => 'failed']);
            $this->voidRsvp($payment);
        }

        $payment->loadMorph('payable', [ProductOrder::class => ['items.product'], Rsvp::class => ['event']]);

        return view('portal.payments.result', [
            'payment' => $payment,
            'status' => 'fail',
            'payload' => $this->resultPayload($payment, 'fail'),
        ]);
    }

    public function cancel(Payment $payment): View
    {
        if (! $payment->isSuccessful()) {
            $payment->update(['status' => 'cancelled']);
            $this->voidRsvp($payment);
        }

        $payment->loadMorph('payable', [ProductOrder::class => ['items.product'], Rsvp::class => ['event']]);

        return view('portal.payments.result', [
            'payment' => $payment,
            'status' => 'cancel',
            'payload' => $this->resultPayload($payment, 'cancel'),
        ]);
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

        if (! $this->ipnSignatureIsValid($request)) {
            Log::warning('IPN signature mismatch — rejected.', ['tran_id' => $tranId]);

            return response()->json(['status' => 'FAILED']);
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

    private function ipnSignatureIsValid(Request $request): bool
    {
        $key = $request->input('verify_key');
        $sign = (string) $request->input('verify_sign', '');

        if (! $key || $sign === '') {
            return true;
        }

        $data = [];

        foreach (explode(',', (string) $key) as $field) {
            if ($request->has($field)) {
                $data[$field] = (string) $request->input($field);
            }
        }

        $data['store_passwd'] = md5((string) config('services.sslcommerz.store_password', ''));

        ksort($data);

        $parts = [];

        foreach ($data as $field => $value) {
            $parts[] = $field.'='.$value;
        }

        $hashString = implode('&', $parts);

        return hash_equals(md5($hashString), strtolower($sign));
    }

    private function resultPayload(Payment $payment, string $status): array
    {
        $payable = $payment->payable;
        $order = $payable instanceof ProductOrder ? $payable : null;
        $rsvp = $order ? null : $payable;
        $event = $order ? null : $rsvp?->event;

        $confirmed = $payment->isSuccessful();

        $headline = match ($status) {
            'success' => $confirmed ? ($order ? 'Order Paid!' : 'Payment Confirmed!') : 'Payment Underway',
            'fail' => 'Payment Didn\'t Land',
            default => 'Payment Cancelled',
        };

        $emoji = match ($status) {
            'success' => $confirmed ? '&#129381;' : '&#8987;',
            'fail' => '&#127922;',
            default => '&#128477;',
        };

        $tone = match ($status) {
            'success' => $confirmed ? 'var(--gold)' : 'var(--sky-dark)',
            'fail' => 'var(--coral)',
            default => 'var(--fog)',
        };

        $copy = match ($status) {
            'success' => $confirmed
                ? ($order
                    ? 'Payment received — your gear is held at the front desk. Show this receipt to collect it.'
                    : 'Your spot is locked in — the club secretary has been notified. See you on the lanes.')
                : 'The payment is still clearing — we\'ll confirm by email the moment it lands. No action needed.',
            'fail' => $order
                ? 'The payment didn\'t go through. Your bag is still there — you can try checkout again.'
                : 'The payment didn\'t go through. Your RSVP has been cleared — head back to the event page and try again.',
            default => $order
                ? 'The payment was cancelled before completion. Your bag is still there if you want to roll again.'
                : 'The payment was cancelled before completion. Your RSVP has been cleared — you can roll again anytime.',
        };

        $statusLabel = match ($payment->status) {
            'success' => 'Paid in full',
            'processing' => 'Payment pending',
            'failed' => 'Declined',
            'cancelled' => 'Cancelled',
            default => 'Pending',
        };

        return [
            'type' => $order ? 'order' : 'rsvp',
            'order' => $order,
            'rsvp' => $rsvp,
            'event' => $event,
            'headline' => $headline,
            'emoji' => $emoji,
            'tone' => $tone,
            'copy' => $copy,
            'status_label' => $statusLabel,
        ];
    }
}
