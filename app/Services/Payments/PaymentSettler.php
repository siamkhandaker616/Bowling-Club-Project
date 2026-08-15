<?php

namespace App\Services\Payments;

use App\Mail\OrderReceipt;
use App\Mail\RsvpConfirmation;
use App\Mail\RsvpReceipt;
use App\Models\Club;
use App\Models\Event;
use App\Models\Payment;
use App\Models\ProductOrder;
use App\Models\Rsvp;
use Illuminate\Support\Facades\DB;
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

        if (($validation['status'] ?? '') !== 'VALID') {
            return false;
        }

        $this->complete($payment, $tranId, $validation);

        return true;
    }

    public function complete(Payment $payment, string $tranId, array $response): void
    {
        DB::transaction(function () use ($payment, $tranId, $response) {
            $payable = $payment->payable;

            if ($payable instanceof ProductOrder) {
                $payable->fulfill();
            } else {
                $event = Event::whereKey($payable?->event_id)->lockForUpdate()->first();
                $event?->increment('current_rsvps');
                $payable?->update(['status' => 'confirmed']);
            }

            $payment->markSuccessful($tranId, $response);
        });

        $payable = $payment->payable;

        if ($payable instanceof Rsvp) {
            $this->notifySecretary($payable);
            Mail::to($payable->visitor_email)->send(new RsvpReceipt($payable));
        } elseif ($payable instanceof ProductOrder) {
            Mail::to($payment->customer_email)->send(new OrderReceipt($payable));
        }
    }

    private function notifySecretary(Rsvp $rsvp): void
    {
        $club = Club::first();

        if ($club && $club->email) {
            Mail::to($club->email)->send(new RsvpConfirmation($rsvp));
        }
    }
}
