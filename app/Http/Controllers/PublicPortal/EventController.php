<?php

namespace App\Http\Controllers\PublicPortal;

use App\Http\Controllers\Controller;
use App\Mail\RsvpConfirmation;
use App\Models\Club;
use App\Models\Event;
use App\Models\Rsvp;
use App\Services\Payments\SslCommerzGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(private SslCommerzGateway $gateway)
    {
    }

    public function index(): View
    {
        $events = Event::orderBy('date')->orderBy('time')->get();

        return view('portal.events.index', compact('events'));
    }

    public function show(Event $event): View
    {
        $isPast = $event->date < now()->toDateString();

        return view('portal.events.show', compact('event', 'isPast'));
    }

    public function rsvp(Request $request, Event $event): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'visitor_name' => ['required', 'string', 'max:120'],
            'visitor_email' => ['required', 'email', 'max:255'],
            'visitor_phone' => ['nullable', 'string', 'max:30'],
        ]);

        $result = DB::transaction(function () use ($event, $data) {
            $locked = Event::whereKey($event->id)->lockForUpdate()->firstOrFail();

            if ($locked->date < now()->toDateString()) {
                return ['error' => 'This event has already rolled.'];
            }

            $existing = Rsvp::where('event_id', $locked->id)
                ->where('visitor_email', $data['visitor_email'])
                ->where('status', '!=', 'cancelled')
                ->get();

            foreach ($existing as $row) {
                $payment = $row->payment;
                $inFlight = $row->status === 'confirmed'
                    || ($row->status === 'pending' && $payment && in_array($payment->status, ['pending', 'processing'], true));

                if ($inFlight) {
                    return ['error' => "You're already on the list for this event."];
                }
            }

            if ($locked->isFull()) {
                return ['error' => 'This event is at full capacity.'];
            }

            $paid = (float) $locked->price > 0;

            $rsvp = Rsvp::create([
                'event_id' => $locked->id,
                'visitor_name' => $data['visitor_name'],
                'visitor_email' => $data['visitor_email'],
                'status' => $paid ? 'pending' : 'confirmed',
            ]);

            if ($paid) {
                $rsvp->payment()->create([
                    'transaction_id' => $this->gateway->generateTransactionId(),
                    'amount' => $locked->price,
                    'currency' => 'BDT',
                    'status' => 'pending',
                    'customer_name' => $data['visitor_name'],
                    'customer_email' => $data['visitor_email'],
                    'customer_phone' => $data['visitor_phone'] ?? null,
                ]);
            } else {
                $locked->increment('current_rsvps');
                $this->notifySecretary($rsvp);
            }

            return ['rsvp' => $rsvp, 'paid' => $paid];
        });

        if (isset($result['error'])) {
            return $this->rsvpError($request, $event, $result['error']);
        }

        $rsvp = $result['rsvp'];
        $paid = $result['paid'];

        if (! $paid) {
            return $this->rsvpResponse($request, $rsvp, null, "You're on the list! See you on the lanes.");
        }

        $payment = $rsvp->payment;

        if (! $this->gateway->isConfigured()) {
            DB::transaction(function () use ($event, $rsvp) {
                $locked = Event::whereKey($event->id)->lockForUpdate()->firstOrFail();
                $locked->increment('current_rsvps');
                $rsvp->update(['status' => 'confirmed']);
            });
            $payment->markSuccessful($payment->transaction_id);
            $this->notifySecretary($rsvp);

            return $this->rsvpResponse($request, $rsvp, $payment, 'Payment received — you\'re confirmed!');
        }

        try {
            $response = $this->gateway->initSession([
                'total_amount' => (string) $payment->amount,
                'currency' => 'BDT',
                'tran_id' => $payment->transaction_id,
                'success_url' => route('public.pay.success', $payment),
                'fail_url' => route('public.pay.fail', $payment),
                'cancel_url' => route('public.pay.cancel', $payment),
                'ipn_url' => route('public.pay.ipn'),
                'cus_name' => $payment->customer_name,
                'cus_email' => $payment->customer_email,
                'cus_phone' => $payment->customer_phone,
                'product_name' => $event->title,
                'product_category' => 'Events & Tickets',
                'product_profile' => 'general',
            ]);
        } catch (\Throwable $e) {
            Log::warning('SSLCommerz session creation failed: '.$e->getMessage());

            $payment->update([
                'status' => 'failed',
                'error_message' => 'The payment gateway could not be reached. Please try again later.',
            ]);

            return $this->rsvpError($request, $event, 'The payment gateway could not be reached. Please try again later.');
        }

        if (($response['status'] ?? '') === 'SUCCESS') {
            $payment->update([
                'status' => 'processing',
                'session_key' => $response['sessionkey'] ?? null,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'redirect_url' => $response['GatewayPageURL'],
                ]);
            }

            return redirect()->away($response['GatewayPageURL']);
        }

        $payment->update([
            'status' => 'failed',
            'error_message' => $response['failedreason'] ?? 'The payment gateway rejected the request.',
        ]);

        return $this->rsvpError($request, $event, $payment->error_message);
    }

    private function rsvpResponse(Request $request, Rsvp $rsvp, $payment, string $message): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            $event = $rsvp->event;

            return response()->json([
                'success' => true,
                'message' => $message,
                'current_rsvps' => $event->current_rsvps,
                'max_capacity' => $event->max_capacity,
                'remaining' => $event->remainingSpots(),
                'paid' => (bool) $payment,
            ]);
        }

        session()->flash('success', $message);

        return redirect()->route('public.events.show', $rsvp->event_id);
    }

    private function rsvpError(Request $request, Event $event, string $message): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return response()->json(['error' => $message], 422);
        }

        session()->flash('error', $message);

        return redirect()->route('public.events.show', $event);
    }

    private function notifySecretary(Rsvp $rsvp): void
    {
        $club = Club::first();

        if ($club && $club->email) {
            Mail::to($club->email)->send(new RsvpConfirmation($rsvp));
        }
    }
}
