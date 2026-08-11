<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\BookingQueue;
use App\Models\Complaint;
use App\Models\Event;
use App\Models\LaneBooking;
use App\Models\Visitor;
use App\Models\VisitorReview;
use App\Services\Simulation\Clock;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $visitor = Visitor::where('user_id', $user->id)->first();

        $nextBooking = $visitor
            ? LaneBooking::with('lane')->where('visitor_id', $visitor->id)->whereIn('status', ['pending', 'confirmed'])->orderBy('date')->first()
            : null;

        $bookings = $visitor
            ? LaneBooking::with('lane')->where('visitor_id', $visitor->id)->orderByDesc('date')->limit(8)->get()
            : collect();

        $queue = $visitor
            ? BookingQueue::with('booking.lane')->where('visitor_id', $visitor->id)->where('status', 'waiting')->orderBy('position')->first()
            : null;

        $myComplaints = $visitor
            ? Complaint::where('visitor_id', $visitor->id)->count()
            : 0;

        $myReviews = $visitor
            ? VisitorReview::where('visitor_id', $visitor->id)->count()
            : 0;

        $events = Event::where('date', '>=', now())->orderBy('date')->limit(3)->get();

        $announcements = \App\Models\Announcement::orderByDesc('created_at')->limit(3)->get();

        return view('dashboards.customer', compact('user', 'visitor', 'nextBooking', 'bookings', 'queue', 'myComplaints', 'myReviews', 'events', 'announcements'));
    }
}
