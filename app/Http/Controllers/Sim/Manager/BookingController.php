<?php

namespace App\Http\Controllers\Sim\Manager;

use App\Http\Controllers\Controller;
use App\Models\BookingQueue;
use App\Models\LaneBooking;
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

        session()->flash('success', 'Booking #' . $booking->id . ' cancelled.');

        return redirect()->route('manager.bookings.index');
    }
}
