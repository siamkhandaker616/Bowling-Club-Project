<?php

namespace App\Http\Controllers\Sim\Manager;

use App\Http\Controllers\Controller;
use App\Models\Bonus;
use App\Models\Penalty;
use App\Models\Personality;
use App\Models\Staff;
use App\Models\StaffEvent;
use App\Models\StaffRelationship;
use App\Models\User;
use App\Services\Simulation\Clock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    private const CONTRADICTIONS = [
        'honest' => ['opportunistic'],
        'overtly_friendly' => ['rude', 'creepy'],
        'nerd' => ['stoner'],
    ];

    public function index()
    {
        $staff = Staff::with('user', 'personalities')
            ->orderBy('is_active', 'desc')
            ->orderBy('role')
            ->orderBy('id')
            ->get();

        $counts = [
            'total' => $staff->count(),
            'active' => $staff->where('is_active', true)->count(),
            'avg_happiness' => round($staff->where('is_active', true)->avg('happiness') ?? 0, 1),
            'low_morale' => $staff->where('is_active', true)->where('happiness', '<', 50)->count(),
        ];

        return view('sim.manager.staff.index', compact('staff', 'counts'));
    }

    private function assignPersonalities(): array
    {
        $names = Personality::pluck('name')->all();
        $target = mt_rand(2, 4);

        for ($attempt = 0; $attempt < 10; $attempt++) {
            shuffle($names);

            $picked = [];
            foreach ($names as $name) {
                if (count($picked) >= $target) {
                    break;
                }

                $conflicts = [];
                foreach ($picked as $have) {
                    $conflicts = array_merge($conflicts, self::CONTRADICTIONS[$have] ?? []);
                }

                if (in_array($name, $conflicts, true) || array_intersect($picked, self::CONTRADICTIONS[$name] ?? [])) {
                    continue;
                }

                $picked[] = $name;
            }

            if (count($picked) === $target) {
                return $picked;
            }
        }

        return $picked ?? [];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', 'in:steward,caretaker'],
            'base_salary' => ['required', 'numeric', 'min:0'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make('password'),
            'role' => $data['role'],
            'email_verified_at' => now(),
            'is_npc' => true,
        ]);

        $staff = Staff::create([
            'user_id' => $user->id,
            'role' => $data['role'],
            'base_salary' => $data['base_salary'],
            'current_salary' => $data['base_salary'],
            'happiness' => 70,
            'performance_score' => 60,
            'honesty_score' => 60,
            'hire_date' => Carbon::today(),
            'is_active' => true,
        ]);

        $traits = $this->assignPersonalities();
        $staff->personalities()->attach(Personality::whereIn('name', $traits)->pluck('id'));

        StaffEvent::create([
            'staff_id' => $staff->id,
            'event_type' => 'hired',
            'severity' => 'positive',
            'description' => 'New hire onboarded as ' . $data['role'] . ' — traits: ' . implode(', ', $traits),
            'date' => Clock::date(),
            'happiness_change' => 5,
        ]);

        session()->flash('success', $data['name'] . ' hired. Traits: ' . implode(', ', $traits) . '.');

        return redirect()->route('manager.staff.show', $staff);
    }

    public function show(Staff $staff)
    {
        $staff->load('user', 'personalities', 'shifts', 'bonuses.issuer', 'penalties.issuer', 'staffEvents');

        $relationships = StaffRelationship::with('staffA.user', 'staffB.user')
            ->where('staff_a_id', $staff->id)
            ->orWhere('staff_b_id', $staff->id)
            ->get();

        return view('sim.manager.staff.show', compact('staff', 'relationships'));
    }

    public function edit(Staff $staff)
    {
        return view('sim.manager.staff.edit', compact('staff'));
    }

    public function update(Request $request, Staff $staff)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'in:steward,caretaker'],
            'base_salary' => ['required', 'numeric', 'min:0'],
            'current_salary' => ['required', 'numeric', 'min:0'],
            'happiness' => ['required', 'integer', 'between:0,100'],
            'performance_score' => ['required', 'integer', 'between:0,100'],
            'honesty_score' => ['required', 'integer', 'between:0,100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $staff->user->update(['name' => $data['name']]);

        $staff->update([
            'role' => $data['role'],
            'base_salary' => $data['base_salary'],
            'current_salary' => $data['current_salary'],
            'happiness' => $data['happiness'],
            'performance_score' => $data['performance_score'],
            'honesty_score' => $data['honesty_score'],
            'is_active' => $request->boolean('is_active'),
        ]);

        session()->flash('success', $staff->user->name . ' updated.');

        return redirect()->route('manager.staff.show', $staff);
    }

    public function destroy(Staff $staff)
    {
        $name = $staff->user->name;

        $staff->update(['is_active' => false]);
        $staff->user->update(['is_active' => false]);

        StaffEvent::create([
            'staff_id' => $staff->id,
            'event_type' => 'fired',
            'severity' => 'negative',
            'description' => 'Employment terminated',
            'date' => Clock::date(),
            'happiness_change' => -20,
        ]);

        session()->flash('success', $name . ' fired. Their account is deactivated.');

        return redirect()->route('manager.staff.index');
    }

    public function bonus(Request $request, Staff $staff)
    {
        $data = $request->validate([
            'type' => ['required', 'in:cash,time_off,recognition'],
            'amount_or_hours' => ['required', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $bonus = Bonus::create([
            'staff_id' => $staff->id,
            'type' => $data['type'],
            'reason' => $data['reason'],
            'amount_or_hours' => $data['amount_or_hours'],
            'date' => Clock::date(),
            'issued_by' => $request->user()->staff?->id,
        ]);

        $lift = match ($data['type']) {
            'recognition' => 10,
            'cash' => 5,
            'time_off' => 3,
        };

        $staff->happiness = max(0, min(100, $staff->happiness + $lift));
        $staff->save();

        StaffEvent::create([
            'staff_id' => $staff->id,
            'event_type' => 'bonus',
            'severity' => 'positive',
            'description' => ucfirst($data['type']) . ' bonus: ' . $data['reason'],
            'date' => Clock::date(),
            'happiness_change' => $lift,
        ]);

        session()->flash('success', ucfirst($data['type']) . " bonus awarded to {$staff->user->name} (+{$lift} happiness).");

        return redirect()->route('manager.staff.show', $staff);
    }

    public function penalty(Request $request, Staff $staff)
    {
        $data = $request->validate([
            'type' => ['required', 'in:pay_dock,extra_hours,written_warning'],
            'amount_or_hours' => ['required', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $penalty = Penalty::create([
            'staff_id' => $staff->id,
            'type' => $data['type'],
            'reason' => $data['reason'],
            'amount_or_hours' => $data['amount_or_hours'],
            'date' => Clock::date(),
            'issued_by' => $request->user()->staff?->id,
        ]);

        $drop = $data['type'] === 'pay_dock' ? -10 : -6;

        $staff->happiness = max(0, min(100, $staff->happiness + $drop));
        $staff->warnings_count = $staff->warnings_count + 1;

        if ($data['type'] === 'pay_dock' && $data['amount_or_hours'] > 0) {
            $staff->current_salary = max(0, $staff->current_salary - $data['amount_or_hours']);
        }

        $staff->save();

        StaffEvent::create([
            'staff_id' => $staff->id,
            'event_type' => 'penalty',
            'severity' => 'negative',
            'description' => ucfirst(str_replace('_', ' ', $data['type'])) . ': ' . $data['reason'],
            'date' => Clock::date(),
            'happiness_change' => $drop,
        ]);

        session()->flash('success', ucfirst(str_replace('_', ' ', $data['type'])) . " issued to {$staff->user->name} ({$drop} happiness).");

        return redirect()->route('manager.staff.show', $staff);
    }
}
