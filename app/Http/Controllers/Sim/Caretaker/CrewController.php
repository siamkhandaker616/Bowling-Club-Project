<?php

namespace App\Http\Controllers\Sim\Caretaker;

use App\Http\Controllers\Controller;
use App\Models\Confrontation;
use App\Models\StaffRelationship;
use App\Services\Simulation\Clock;
use App\Services\Simulation\DialogueService;
use App\Services\Simulation\SocialEngine;
use Illuminate\Http\Request;

class CrewController extends Controller
{
    public function index(Request $request)
    {
        $me = $request->user()->staff;

        $relationships = StaffRelationship::with('staffA.user', 'staffB.user')
            ->where('staff_a_id', $me->id)
            ->orWhere('staff_b_id', $me->id)
            ->get();

        $confrontations = Confrontation::with('reporter.user', 'accused.user')
            ->where('reporter_staff_id', $me->id)
            ->orWhere('accused_staff_id', $me->id)
            ->orderByDesc('created_at')
            ->get();

        $dialogue = app(DialogueService::class);
        $myBubbles = $dialogue->bubblesFor($me, Clock::date());

        $coworkerBubbles = $relationships->take(3)->map(function ($rel) use ($me, $dialogue) {
            $other = $rel->staffA?->id === $me->id ? $rel->staffB : $rel->staffA;

            return $other ? [
                'name' => $other->user->name ?? 'Coworker',
                'bubbles' => $dialogue->bubblesFor($other, Clock::date()),
            ] : null;
        })->filter()->values();

        return view('sim.caretaker.crew.index', compact('me', 'relationships', 'confrontations', 'myBubbles', 'coworkerBubbles'));
    }

    public function vent(Request $request)
    {
        $me = $request->user()->staff;

        if (! $me || ! $me->is_active) {
            abort(403);
        }

        $log = [];
        app(SocialEngine::class)->vent($me, Clock::date(), $log);

        session()->flash('success', count($log['snitches'] ?? []) > 0
            ? 'You vented... and somebody snitched on you. Expect a confrontation.'
            : 'You got that off your chest. Feels good... for now.');

        return redirect()->route('caretaker.crew.index');
    }
}
