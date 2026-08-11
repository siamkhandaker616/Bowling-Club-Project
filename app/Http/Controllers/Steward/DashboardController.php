<?php

namespace App\Http\Controllers\Steward;

use App\Http\Controllers\Controller;
use App\Models\LaneBooking;
use App\Models\Shift;
use App\Models\Visitor;
use App\Services\Simulation\Clock;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $date = Clock::date();

        $shifts = Shift::with('staff.user')->whereDate('date', $date)->orderBy('time_slot')->get();
        $bookings = LaneBooking::with('visitor', 'lane')->whereDate('date', $date)->orderBy('time_slot')->get();
        $visitors = Visitor::withCount('bookings')->orderBy('name')->get();

        $stats = [
            'staff_on_duty' => $shifts->pluck('staff_id')->unique()->count(),
            'today_bookings' => $bookings->count(),
            'checked_in' => $bookings->where('status', 'confirmed')->count(),
            'total_visitors' => $visitors->count(),
            'banned' => $visitors->where('is_banned', true)->count(),
            'premium' => $visitors->where('tier', 'premium')->count(),
        ];

        return view('dashboards.steward', compact('user', 'date', 'shifts', 'bookings', 'visitors', 'stats'));
    }
}
