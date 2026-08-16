<?php

namespace App\Services\Payments;

use App\Mail\OrderReceipt;
use App\Mail\RsvpConfirmation;
use App\Mail\RsvpReceipt;
use App\Models\CartItem;
use App\Models\Club;
use App\Models\Event;
use App\Models\Payment;
use App\Models\ProductOrder;
use App\Models\Rsvp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentSettler
{
    public function __construct(private SslCommerzGateway $gateway)
    {
    }

    public function settleIfPossible(Payment $payment): bool
    {
        if (! $this->gateway->isConfigured() || ! $payment->session_key) {
            return false;
        }

        $tranId = (string) $payment->transaction_id;

        $validation = $this->gateway->validate($payment->session_key, $tranId);

        if (($validation['status'] ?? '') === 'VALID') {
            return $this->complete($payment, $tranId, $validation);
        }

        if ($this->isStale($payment)) {
            $this->expire($payment, 'Payment session expired before the payment was confirmed.');
        }

        return false;
    }

    public function complete(Payment $payment, string $tranId, array $response): bool
    {
        $completed = DB::transaction(function () use ($payment, $tranId, $response) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->first();

            if (! $locked || $locked->status !== 'processing') {
                return false;
            }

            $payable = $locked->payable;

            if ($payable instanceof ProductOrder) {
                $payable->fulfill();
            } else {
                $event = Event::whereKey($payable?->event_id)->lockForUpdate()->first();

                if (! $event) {
                    return false;
                }

                $event->increment('current_rsvps');
                $payable?->update(['status' => 'confirmed']);
            }

            $locked->markSuccessful($tranId, $response);

            return true;
        });

        if (! $completed) {
            return false;
        }

        $payable = $payment->payable;

        if ($payable instanceof Rsvp) {
            $this->notifySecretary($payable);

            try {
                Mail::to($payable->visitor_email)->send(new RsvpReceipt($payable));
            } catch (\Throwable $e) {
                Log::warning('RSVP receipt email failed: '.$e->getMessage());
            }
        } elseif ($payable instanceof ProductOrder) {
            $payable->load(['items.product', 'payment']);

            try {
                Mail::to($payment->customer_email)->send(new OrderReceipt($payable));
            } catch (\Throwable $e) {
                Log::warning('Order receipt email failed: '.$e->getMessage());
            }

            $this->clearCartFor($payment, null);
        }

        return true;
    }

    public function expire(Payment $payment, string $reason): void
    {
        DB::transaction(function () use ($payment, $reason) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->first();

            if (! $locked || ! in_array($locked->status, ['pending', 'processing'], true)) {
                return;
            }

            $locked->update(['status' => 'failed', 'error_message' => $reason]);

            $payable = $locked->payable;

            if ($payable instanceof Rsvp && ! $payable->isConfirmed()) {
                $payable->update(['status' => 'cancelled']);
            }
        });
    }

    public function clearCartFor(Payment $payment, ?string $fallbackSession): void
    {
        $sessionId = $payment->response['session_id'] ?? $fallbackSession;

        if (! $sessionId) {
            return;
        }

        CartItem::where('session_id', $sessionId)->delete();
    }

    private function isStale(Payment $payment): bool
    {
        return $payment->updated_at && $payment->updated_at->lt(now()->subHours(24));
    }

    private function notifySecretary(Rsvp $rsvp): void
    {
        $club = Club::first();

        if ($club && $club->email) {
            try {
                Mail::to($club->email)->send(new RsvpConfirmation($rsvp));
            } catch (\Throwable $e) {
                Log::warning('Secretary notification email failed: '.$e->getMessage());
            }
        }
    }
}
