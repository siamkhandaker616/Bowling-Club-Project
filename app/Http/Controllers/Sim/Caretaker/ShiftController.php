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

        $weekStart = now()->copy()->startOfWeek();
        $weekEnd = now()->copy()->endOfWeek();
        $weekShifts = Shift::with('lane')
            ->where('staff_id', $staff->id)
            ->whereDate('date', '>=', $weekStart->toDateString())
            ->whereDate('date', '<=', $weekEnd->toDateString())
            ->get();
        $weekLabel = $weekStart->format('M j') . ' – ' . $weekEnd->format('M j');
        $weekTotal = $weekShifts->count();
        $weekDone = $weekShifts->where('status', 'completed')->count();

        return view('sim.caretaker.shifts.index', compact('shifts', 'weekLabel', 'weekTotal', 'weekDone'));
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
