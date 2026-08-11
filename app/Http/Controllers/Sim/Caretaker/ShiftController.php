<?php

namespace App\Http\Controllers\Sim\Caretaker;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Services\Simulation\DayCycle;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $staff = $request->user()->staff;

        $shifts = Shift::with('lane')->where('staff_id', $staff->id)->orderBy('date', 'desc')->limit(30)->get();

        return view('sim.caretaker.shifts.index', compact('shifts'));
    }

    public function complete(Request $request, Shift $shift)
    {
        if ($shift->staff_id !== $request->user()->staff->id) {
            abort(403);
        }

        app(DayCycle::class)->markShiftComplete($shift);

        session()->flash('success', 'Shift marked complete. Nice work.');

        return redirect()->route('caretaker.shifts.index');
    }

    public function cancel(Request $request, Shift $shift)
    {
        if ($shift->staff_id !== $request->user()->staff->id) {
            abort(403);
        }

        $shift->update(['status' => 'cancelled']);

        session()->flash('success', 'Shift cancelled. The day-cycle will re-schedule staff.');

        return redirect()->route('caretaker.shifts.index');
    }
}
