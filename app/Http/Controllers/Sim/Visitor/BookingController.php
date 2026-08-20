<?php

namespace App\Http\Controllers\Sim\Visitor;

use App\Http\Controllers\Controller;
use App\Models\BookingQueue;
use App\Models\ClubConfig;
use App\Models\Lane;
use App\Models\LaneBooking;
use App\Models\Payment;
use App\Models\Visitor;
use App\Services\Payments\SslCommerzGateway;
use App\Services\Simulation\Clock;
use App\Services\Simulation\VisitorRegistry;
use App\Services\Simulation\VisitorSpawner;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function __construct(private SslCommerzGateway $gateway)
    {
    }

    public function create(Request $request)
    {
        $visitor = $this->visitor($request->user());

        if (! $visitor) {
            return view('sim.visitor.bookings.create', ['visitor' => null, 'lanes' => collect(), 'slots' => [], 'date' => null]);
        }

        $lanes = Lane::orderBy('lane_number')->get();
        $slots = Clock::timeSlots();
        $date = Clock::date()->copy()->addDay();
        $bookingPrice = (float) ClubConfig::singleton()->lane_booking_price;

        $selectedLaneId = null;
        if ($request->filled('lane')) {
            $selectedLaneId = Lane::where('lane_number', $request->integer('lane'))->value('id');
        }

        return view('sim.visitor.bookings.create', compact('visitor', 'lanes', 'slots', 'date', 'selectedLaneId', 'bookingPrice'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lane_id' => ['required', 'exists:lanes,id'],
            'time_slot' => ['required', 'in:' . implode(',', array_keys(Clock::timeSlots()))],
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $visitor = $this->visitor($request->user());

        if (! $visitor) {
            abort(403, 'No visitor profile linked to this account.');
        }

        if ($visitor->is_banned) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'You are banned from booking lanes.'], 403);
            }

            session()->flash('error', 'You are banned and cannot book lanes.');

            return redirect()->route('visitor.bookings.create');
        }

        $lane = Lane::findOrFail($data['lane_id']);

        $existing = LaneBooking::where('visitor_id', $visitor->id)
            ->whereDate('date', $data['date'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($existing) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'You already have an active booking for that date.'], 422);
            }

            session()->flash('error', 'You already have an active booking for that date.');

            return redirect()->route('visitor.bookings.create');
        }

        $laneTaken = LaneBooking::where('lane_id', $lane->id)
            ->whereDate('date', $data['date'])
            ->where('time_slot', $data['time_slot'])
            ->where('status', 'confirmed')
            ->exists();

        if ($laneTaken) {
            $position = (BookingQueue::whereDate('date', $data['date'])->max('position') ?? 0) + 1;

            $booking = LaneBooking::create([
                'visitor_id' => $visitor->id,
                'lane_id' => $lane->id,
                'date' => $data['date'],
                'time_slot' => $data['time_slot'],
                'status' => 'pending',
                'queue_position' => $position,
            ]);

            BookingQueue::create([
                'booking_id' => $booking->id,
                'visitor_id' => $visitor->id,
                'lane_id' => $lane->id,
                'date' => $data['date'],
                'time_slot' => $data['time_slot'],
                'position' => $position,
                'status' => 'waiting',
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lane is busy — you were added to the waiting queue at position ' . $position . '.',
                    'redirect' => route('visitor.queues.index'),
                ]);
            }

            session()->flash('success', 'Lane is busy — you were added to the waiting queue at position ' . $position . '.');

            return redirect()->route('visitor.queues.index');
        }

        $bookingPrice = (float) ClubConfig::singleton()->lane_booking_price;

        if ($bookingPrice <= 0) {
            LaneBooking::create([
                'visitor_id' => $visitor->id,
                'lane_id' => $lane->id,
                'date' => $data['date'],
                'time_slot' => $data['time_slot'],
                'status' => 'confirmed',
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lane ' . $lane->lane_number . ' booked for ' . $data['date'] . ' (' . Clock::timeSlots()[$data['time_slot']] . ').',
                    'redirect' => route('visitor.bookings.index'),
                ]);
            }

            session()->flash('success', 'Lane ' . $lane->lane_number . ' booked for ' . $data['date'] . ' (' . Clock::timeSlots()[$data['time_slot']] . ').');

            return redirect()->route('visitor.bookings.index');
        }

        $booking = LaneBooking::create([
            'visitor_id' => $visitor->id,
            'lane_id' => $lane->id,
            'date' => $data['date'],
            'time_slot' => $data['time_slot'],
            'status' => 'pending',
            'amount' => $bookingPrice,
        ]);

        $payment = Payment::create([
            'payable_type' => LaneBooking::class,
            'payable_id' => $booking->id,
            'transaction_id' => $this->gateway->generateTransactionId(),
            'amount' => $bookingPrice,
            'currency' => 'BDT',
            'status' => 'pending',
            'customer_name' => $visitor->name ?? $request->user()->name,
            'customer_email' => $visitor->email ?? $request->user()->email,
        ]);

        if (! $this->gateway->isConfigured()) {
            $payment->update(['status' => 'processing']);
            app(\App\Services\Payments\PaymentSettler::class)->complete($payment, $payment->transaction_id, ['status' => 'VALID']);
            $payment->refresh();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lane ' . $lane->lane_number . ' booked (simulated payment).',
                    'redirect' => route('visitor.bookings.index'),
                ]);
            }

            session()->flash('success', 'Lane ' . $lane->lane_number . ' booked (simulated payment).');

            return redirect()->route('visitor.bookings.index');
        }

        try {
            $response = $this->gateway->initSession([
                'total_amount' => (string) $bookingPrice,
                'currency' => 'BDT',
                'tran_id' => $payment->transaction_id,
                'success_url' => route('public.pay.success', $payment),
                'fail_url' => route('public.pay.fail', $payment),
                'cancel_url' => route('public.pay.cancel', $payment),
                'ipn_url' => route('public.pay.ipn'),
                'cus_name' => $visitor->name ?? $request->user()->name,
                'cus_email' => $visitor->email ?? $request->user()->email,
                'product_name' => 'Lane booking: Lane ' . $lane->lane_number . ' — ' . $data['date'] . ' ' . Clock::timeSlots()[$data['time_slot']],
                'product_category' => 'Lane Reservation',
                'product_profile' => 'general',
            ]);
        } catch (\Throwable $e) {
            Log::warning('SSLCommerz session failed for lane booking: ' . $e->getMessage());
            $payment->update(['status' => 'failed', 'error_message' => 'Payment service unreachable.']);
            $booking->update(['status' => 'cancelled']);

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Payment service unreachable — booking cancelled.'], 502);
            }

            session()->flash('error', 'Payment service unreachable — booking cancelled.');

            return redirect()->route('visitor.bookings.create');
        }

        if (($response['status'] ?? '') !== 'SUCCESS') {
            $payment->update(['status' => 'failed', 'error_message' => $response['failedreason'] ?? 'Payment session declined.']);
            $booking->update(['status' => 'cancelled']);

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Payment gateway declined the session.'], 502);
            }

            session()->flash('error', 'Payment gateway declined the session.');

            return redirect()->route('visitor.bookings.create');
        }

        $payment->update(['status' => 'processing', 'session_key' => $response['sessionkey'] ?? null]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'gateway_url' => $response['GatewayPageURL'],
                'payment_id' => $payment->id,
            ]);
        }

        return redirect()->away($response['GatewayPageURL']);
    }

    public function index(Request $request)
    {
        $visitor = $this->visitor($request->user());

        $bookings = $visitor
            ? LaneBooking::with('lane')->where('visitor_id', $visitor->id)->orderByDesc('date')->get()
            : collect();

        return view('sim.visitor.bookings.index', compact('bookings'));
    }

    public function cancel(Request $request, LaneBooking $booking)
    {
        $visitor = $this->visitor($request->user());

        if (! $visitor || $booking->visitor_id !== $visitor->id) {
            abort(403);
        }

        $booking->update(['status' => 'cancelled']);
        $booking->queueEntries()->delete();

        $log = [];
        app(VisitorSpawner::class)->promoteForSlot(Carbon::parse($booking->date), $booking->time_slot, $log);

        session()->flash('success', 'Booking cancelled.' . ((($log['queues_promoted'] ?? 0) > 0) ? ' The next visitor in the queue was promoted to your lane.' : ''));

        return redirect()->route('visitor.bookings.index');
    }

    public function status(Payment $payment): JsonResponse
    {
        $payment->refresh();

        return response()->json([
            'status' => $payment->status,
            'successful' => $payment->isSuccessful(),
        ]);
    }

    private function visitor($user): ?Visitor
    {
        return app(VisitorRegistry::class)->forUser($user);
    }
}
