<?php

namespace App\Http\Controllers\Sim\Visitor;

use App\Http\Controllers\Controller;
use App\Models\BookingQueue;
use App\Models\Lane;
use App\Models\LaneBooking;
use App\Models\Visitor;
use App\Services\Simulation\Clock;
use App\Services\Simulation\VisitorSpawner;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function create(Request $request)
    {
        $visitor = $this->visitor($request->user());

        if (! $visitor) {
            return view('sim.visitor.bookings.create', ['visitor' => null, 'lanes' => collect(), 'slots' => [], 'date' => null]);
        }

        $lanes = Lane::orderBy('lane_number')->get();
        $slots = Clock::timeSlots();
        $date = Clock::date()->copy()->addDay();

        return view('sim.visitor.bookings.create', compact('visitor', 'lanes', 'slots', 'date'));
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
            session()->flash('error', 'You are banned and cannot book lanes.');

            return redirect()->route('visitor.bookings.create');
        }

        $lane = Lane::findOrFail($data['lane_id']);

        $existing = LaneBooking::where('visitor_id', $visitor->id)
            ->whereDate('date', $data['date'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($existing) {
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

            session()->flash('success', 'Lane is busy — you were added to the waiting queue at position ' . $position . '.');

            return redirect()->route('visitor.queues.index');
        }

        LaneBooking::create([
            'visitor_id' => $visitor->id,
            'lane_id' => $lane->id,
            'date' => $data['date'],
            'time_slot' => $data['time_slot'],
            'status' => 'confirmed',
        ]);

        session()->flash('success', 'Lane ' . $lane->lane_number . ' booked for ' . $data['date'] . ' (' . Clock::timeSlots()[$data['time_slot']] . ').');

        return redirect()->route('visitor.bookings.index');
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

        session()->flash('success', 'Booking cancelled.');

        return redirect()->route('visitor.bookings.index');
    }

    private function visitor($user): ?Visitor
    {
        return Visitor::where('user_id', $user->id)->first();
    }
}
