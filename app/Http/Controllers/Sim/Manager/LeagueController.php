<?php

namespace App\Http\Controllers\Sim\Manager;

use App\Http\Controllers\Controller;
use App\Models\Fixture;
use App\Models\League;
use App\Services\Simulation\Clock;
use App\Services\Simulation\MatchService;
use Illuminate\Http\Request;

class LeagueController extends Controller
{
    public function __construct(private MatchService $matches)
    {
    }

    public function index()
    {
        $today = Clock::date();

        $upcoming = $this->matches->upcomingFixtures($today)->map(fn ($f) => [
            'fixture' => $f,
            'ready' => $this->matches->readiness($f),
        ]);

        $live = \App\Models\Fixture::with(['homeTeam', 'awayTeam', 'league', 'lane'])
            ->where('status', 'live')
            ->orderBy('date')
            ->get();

        $leagues = League::with(['teams' => fn ($q) => $q->orderByDesc('wins')])->get();

        return view('sim.manager.league.index', compact('today', 'upcoming', 'live', 'leagues'));
    }

    public function welcome(Request $request, Fixture $fixture)
    {
        if ($fixture->status !== 'upcoming') {
            session()->flash('error', 'This fixture is no longer upcoming.');

            return redirect()->route('manager.league.index');
        }

        $staff = $request->user()->staff;
        $result = $this->matches->welcome($fixture, $staff?->id);

        session()->flash($result['ok'] ? 'success' : 'error', $result['message']);

        return redirect()->route('manager.league.index');
    }
}
