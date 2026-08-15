<?php

namespace App\Http\Controllers\Sim\Steward;

use App\Http\Controllers\Controller;
use App\Models\Bonus;
use App\Models\SnitchReport;
use App\Models\StaffEvent;
use App\Services\Simulation\Clock;
use App\Services\Simulation\ConfrontationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SnitchController extends Controller
{
    public function index()
    {
        $pending = SnitchReport::with('reporter.user', 'accused.user')
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();

        $recent = SnitchReport::with('reporter.user', 'accused.user', 'confrontation')
            ->where('status', '!=', 'pending')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('sim.steward.snitch.index', compact('pending', 'recent'));
    }

    public function escalate(Request $request, SnitchReport $report): RedirectResponse
    {
        if ($report->status !== 'pending') {
            session()->flash('error', 'That report has already been handled.');

            return redirect()->route('steward.snitch.index');
        }

        $validated = $request->validate(['note' => ['nullable', 'string', 'max:300']]);
        $note = (string) ($validated['note'] ?? '');

        $description = trim('Overheard: "' . ($report->quote ?? 'a coworker trash-talking management') . '"' . ($note !== '' ? " Steward note: $note" : ''));

        $confrontation = app(ConfrontationService::class)->create(
            $report->reporter_staff_id,
            $report->accused_staff_id,
            'other',
            $description,
            true,
        );

        $report->update([
            'status' => 'escalated',
            'confrontation_id' => $confrontation->id,
            'steward_note' => $note !== '' ? $note : null,
            'escalated_at' => now(),
        ]);

        $reporter = $report->reporter;
        if ($reporter) {
            $reporter->happiness = max(0, min(100, $reporter->happiness + 5));
            $reporter->save();

            Bonus::create([
                'staff_id' => $reporter->id,
                'type' => 'recognition',
                'reason' => 'Snitch report escalated by the steward',
                'amount_or_hours' => 0,
                'date' => Clock::date(),
                'issued_by' => null,
            ]);

            StaffEvent::create([
                'staff_id' => $reporter->id,
                'event_type' => 'bonus',
                'severity' => 'positive',
                'description' => 'Snitch report validated and escalated to the manager',
                'date' => Clock::date(),
                'happiness_change' => 5,
            ]);
        }

        session()->flash('success', 'Report escalated to the manager — a confrontation was opened.');

        return redirect()->route('steward.snitch.index');
    }

    public function dismiss(Request $request, SnitchReport $report): RedirectResponse
    {
        if ($report->status !== 'pending') {
            session()->flash('error', 'That report has already been handled.');

            return redirect()->route('steward.snitch.index');
        }

        $report->update(['status' => 'dismissed', 'resolved_at' => now()]);

        session()->flash('success', 'Report dismissed — no confrontation opened.');

        return redirect()->route('steward.snitch.index');
    }
}
