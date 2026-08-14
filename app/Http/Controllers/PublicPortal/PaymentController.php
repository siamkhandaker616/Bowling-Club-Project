<?php

namespace App\Http\Controllers\PublicPortal;

use App\Http\Controllers\Controller;
use App\Mail\RsvpConfirmation;
use App\Models\Club;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Rsvp;
use App\Services\Payments\SslCommerzGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private SslCommerzGateway $gateway)
    {
    }

    public function success(Payment $payment): View
    {
        return view('portal.payments.result', ['payment' => $payment, 'status' => 'success']);
    }

    public function fail(Payment $payment): View
    {
        $payment->update(['status' => 'failed']);

        return view('portal.payments.result', ['payment' => $payment, 'status' => 'fail']);
    }

    public function cancel(Payment $payment): View
    {
        $payment->update(['status' => 'cancelled']);

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

        if (! $this->gateway->isConfigured()) {
            return response()->json(['status' => 'FAILED']);
        }

        $validation = $this->gateway->validate($payment->session_key, $tranId);

        if (($validation['status'] ?? '') === 'VALID') {
            $this->completePayment($payment, $tranId, $validation);

            return response()->json(['status' => 'VALID']);
        }

        return response()->json(['status' => 'FAILED']);
    }

    private function completePayment(Payment $payment, string $tranId, array $response): void
    {
        DB::transaction(function () use ($payment, $tranId, $response) {
            $rsvp = $payment->payable;

            $event = Event::whereKey($rsvp?->event_id)->lockForUpdate()->first();
            $event?->increment('current_rsvps');

            $rsvp?->update(['status' => 'confirmed']);
            $payment->markSuccessful($tranId, $response);
        });

        $rsvp = $payment->payable;

        if ($rsvp) {
            $this->notifySecretary($rsvp);
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
