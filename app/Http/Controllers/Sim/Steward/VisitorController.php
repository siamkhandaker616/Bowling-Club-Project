<?php

namespace App\Http\Controllers\Sim\Steward;

use App\Http\Controllers\Controller;
use App\Models\LaneBooking;
use App\Models\Visitor;

class VisitorController extends Controller
{
    public function index()
    {
        $visitors = Visitor::with('bookings')->orderBy('name')->get();

        $checkIns = LaneBooking::with('visitor', 'lane')
            ->where('status', 'confirmed')
            ->orderBy('time_slot')
            ->limit(40)
            ->get();

        return view('sim.steward.visitors.index', compact('visitors', 'checkIns'));
    }
}
