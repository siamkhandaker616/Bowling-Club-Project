<?php

namespace App\Http\Controllers\Sim\Steward;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\StaffEvent;
use App\Services\Simulation\Clock;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index()
    {
        $crew = Staff::with('user')
            ->where('is_active', true)
            ->orderBy('role')
            ->orderBy('id')
            ->get();

        $dailyCost = round($crew->sum('current_salary') / 30, 2);
        $cuts = $crew->filter(fn ($s) => (float) $s->current_salary < (float) $s->base_salary)->count();

        return view('sim.steward.payroll.index', compact('crew', 'dailyCost', 'cuts'));
    }

    public function update(Request $request, Staff $staff)
    {
        $data = $request->validate([
            'salary' => ['required', 'numeric', 'min:0'],
        ]);

        if (! $staff->is_active) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'Only active crew can be adjusted.'], 409);
            }

            abort(409, 'Only active crew can be adjusted.');
        }

        $cap = round((float) $staff->base_salary * 1.5, 2);
        $old = round((float) $staff->current_salary, 2);
        $new = round(min((float) $data['salary'], $cap), 2);
        $capped = (float) $data['salary'] > $cap;

        $staff->current_salary = $new;
        $staff->save();

        $raise = $new > $old;
        $lift = $raise ? 3 : -4;

        if ($new !== $old) {
            $staff->happiness = max(0, min(100, $staff->happiness + $lift));
            $staff->save();

            StaffEvent::create([
                'staff_id' => $staff->id,
                'event_type' => $raise ? 'bonus' : 'penalty',
                'severity' => $raise ? 'positive' : 'negative',
                'description' => ($raise ? 'Salary raised from $' : 'Salary cut from $')
                    .number_format($old, 0).' to $'.number_format($new, 0).' by the steward.',
                'date' => Clock::date(),
                'happiness_change' => $lift,
            ]);
        }

        $message = $capped
            ? 'Set to the ceiling of $'.number_format($cap, 0).' (1.5x base).'
            : 'Payroll updated for '.($staff->user->name ?? 'staff').': $'.number_format($new, 0).'/mo.';

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'salary' => $new,
                'base_salary' => (float) $staff->base_salary,
                'capped' => $capped,
                'message' => $message,
            ]);
        }

        session()->flash('success', $message);

        return redirect()->route('steward.payroll.index');
    }
}
