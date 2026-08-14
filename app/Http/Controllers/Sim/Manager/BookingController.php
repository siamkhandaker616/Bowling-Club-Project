<?php

namespace App\Http\Controllers\Sim\Manager;

use App\Http\Controllers\Controller;
use App\Models\BookingQueue;
use App\Models\LaneBooking;
use App\Services\Simulation\VisitorSpawner;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date');

        $bookings = LaneBooking::with('visitor', 'lane')
            ->when($date, fn ($q) => $q->whereDate('date', $date))
            ->orderBy('date', 'desc')
            ->orderBy('time_slot')
            ->limit(100)
            ->get();

        $queues = BookingQueue::with('visitor', 'booking')
            ->orderBy('date')
            ->orderBy('position')
            ->limit(50)
            ->get();

        return view('sim.manager.bookings.index', compact('bookings', 'queues'));
    }

    public function cancel(Request $request, LaneBooking $booking)
    {
        $booking->status = 'cancelled';
        $booking->save();
        $booking->queueEntries()->delete();

        $log = [];
        app(VisitorSpawner::class)->promoteForSlot(Carbon::parse($booking->date), $booking->time_slot, $log);

        session()->flash('success', 'Booking #' . $booking->id . ' cancelled.' . ($log['queues_promoted'] ?? 0 > 0 ? ' The next visitor in the queue was promoted to that lane.' : ''));

        return redirect()->route('manager.bookings.index');
    }
}
