<?php

namespace App\Http\Controllers\PublicPortal;

use App\Http\Controllers\Controller;
use App\Models\LaneBooking;
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
    public function __construct(private PaymentSettler $settler) {}

    public function success(Request $request, Payment $payment): View
    {
        $sessionId = $payment->response['session_id'] ?? $request->session()->getId();

        if (! $payment->isSuccessful() && $payment->status === 'processing') {
            $postStatus = strtolower((string) $request->input('status', ''));

            if (in_array($postStatus, ['valid', 'success'], true)) {
                $payload = $request->only([
                    'tran_id', 'amount', 'card_type', 'card_no', 'bank_tran_id',
                    'status', 'tran_date', 'currency', 'store_amount',
                ]);

                $this->settler->complete($payment, (string) $payment->transaction_id, $payload);
                $payment->refresh();
            } else {
                try {
                    $this->settler->settleIfPossible($payment);
                    $payment->refresh();
                } catch (\Throwable $e) {
                    Log::warning('SSLCommerz validation failed on public callback: '.$e->getMessage());
                }
            }
        }

        $payment->loadMorph('payable', [LaneBooking::class => ['lane'], ProductOrder::class => ['items.product'], Rsvp::class => ['event']]);

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

        $payment->loadMorph('payable', [LaneBooking::class => ['lane'], ProductOrder::class => ['items.product'], Rsvp::class => ['event']]);

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

        $payment->loadMorph('payable', [LaneBooking::class => ['lane'], ProductOrder::class => ['items.product'], Rsvp::class => ['event']]);

        return view('portal.payments.result', [
            'payment' => $payment,
            'status' => 'cancel',
            'payload' => $this->resultPayload($payment, 'cancel'),
        ]);
    }

    public function status(Payment $payment): JsonResponse
    {
        $payment->refresh();

        return response()->json([
            'status' => $payment->status,
            'successful' => $payment->isSuccessful(),
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
        $booking = ! $order && $payable instanceof LaneBooking ? $payable : null;
        $rsvp = $order || $booking ? null : $payable;
        $event = $order || $booking ? null : $rsvp?->event;

        $confirmed = $payment->isSuccessful();

        $headline = match ($status) {
            'success' => $confirmed ? ($order ? 'Order Paid!' : ($booking ? 'Lane Booked!' : 'Payment Confirmed!')) : 'Payment Underway',
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
                ? match (true) {
                    $order !== null => 'Payment received — your gear is held at the front desk. Show this receipt to collect it.',
                    $booking !== null => 'Your lane is locked in. See you on the approach!',
                    default => 'Your spot is locked in — the club secretary has been notified. See you on the lanes.'
                }
            : 'The payment is still clearing — we\'ll confirm by email the moment it lands. No action needed.',
            'fail' => $order
                ? 'The payment didn\'t go through. Your bag is still there — you can try checkout again.'
                : ($booking
                    ? 'The payment didn\'t go through. Your booking is still pending — head to My Bookings and press Pay Now to retry.'
                    : 'The payment didn\'t go through. Your RSVP has been cleared — head back to the event page and try again.'),
            default => $order
                ? 'The payment was cancelled before completion. Your bag is still there if you want to roll again.'
                : ($booking
                    ? 'The payment was cancelled before completion. Your booking is still pending — you can pay from My Bookings anytime.'
                    : 'The payment was cancelled before completion. Your RSVP has been cleared — you can roll again anytime.')
        };

        $statusLabel = match ($payment->status) {
            'success' => 'Paid in full',
            'processing' => 'Payment pending',
            'failed' => 'Declined',
            'cancelled' => 'Cancelled',
            default => 'Pending',
        };

        return [
            'type' => $order ? 'order' : ($booking ? 'booking' : 'rsvp'),
            'order' => $order,
            'booking' => $booking,
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
