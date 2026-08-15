<?php

namespace App\Http\Controllers\Sim\Steward;

use App\Http\Controllers\Controller;
use App\Models\LaneBooking;
use App\Models\Shift;
use App\Models\Staff;
use App\Services\Simulation\Clock;
use App\Services\Simulation\DayCycle;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $date = Clock::date();

        $shifts = Shift::with('staff.user')->whereDate('date', $date)->orderBy('time_slot')->get();
        $bookings = LaneBooking::with('visitor', 'lane')->whereDate('date', $date)->orderBy('time_slot')->get();
        $staffOnDuty = $shifts->map(fn ($s) => $s->staff->id)->unique()->count();

        return view('sim.steward.schedule.index', compact('date', 'shifts', 'bookings', 'staffOnDuty'));
    }

    public function complete(Request $request, Shift $shift)
    {
        if ($shift->staff->user_id !== $request->user()->id && ! $request->user()->isSteward()) {
            abort(403);
        }

        app(DayCycle::class)->markShiftComplete($shift);

        session()->flash('success', 'Shift marked complete.');

        return redirect()->route('steward.schedule.index');
    }
}
