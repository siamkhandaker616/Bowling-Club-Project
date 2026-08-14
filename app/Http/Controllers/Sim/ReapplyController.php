<?php

namespace App\Http\Controllers\Sim;

use App\Http\Controllers\Controller;
use App\Models\StaffEvent;
use App\Services\Simulation\Clock;
use Illuminate\Http\Request;

class ReapplyController extends Controller
{
    public function index(Request $request)
    {
        $staff = $request->user()->staff;

        if (! $staff || $staff->is_active) {
            return redirect()->route('dashboard');
        }

        return view('sim.reapply.index', compact('staff'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'in:caretaker,steward'],
        ]);

        $user = $request->user();
        $staff = $user->staff;

        if (! $staff || $staff->is_active) {
            abort(403);
        }

        $user->name = $data['name'];
        $user->role = $data['role'];
        $user->is_active = true;
        $user->save();

        $staff->role = $data['role'];
        $staff->happiness = 70;
        $staff->performance_score = 50;
        $staff->honesty_score = 60;
        $staff->hire_date = Clock::date();
        $staff->is_active = true;
        $staff->save();

        StaffEvent::create([
            'staff_id' => $staff->id,
            'event_type' => 'hired',
            'severity' => 'positive',
            'description' => 'Reapplied with a fresh identity as ' . $data['role'],
            'date' => Clock::date(),
            'happiness_change' => 5,
        ]);

        session()->flash('success', 'Welcome back — fresh start as ' . $data['role'] . '.');

        return redirect($data['role'] === 'steward' ? route('steward.dashboard') : route('caretaker.dashboard'));
    }
}
