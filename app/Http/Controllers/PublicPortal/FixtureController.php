<?php

namespace App\Http\Controllers\PublicPortal;

use App\Http\Controllers\Controller;
use App\Models\Fixture;
use App\Models\League;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FixtureController extends Controller
{
    public function index(Request $request): View
    {
        $query = Fixture::with(['homeTeam.league', 'awayTeam.league', 'league', 'lane'])
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc');

        if ($request->filled('league_id')) {
            $query->where('league_id', $request->league_id);
        }

        if ($request->filled('team_id')) {
            $query->where(fn ($q) => $q->where('home_team_id', $request->team_id)
                ->orWhere('away_team_id', $request->team_id));
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $fixtures = $query->get();

        $nextMatch = Fixture::with(['homeTeam', 'awayTeam', 'league'])
            ->where('status', 'upcoming')
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('time')
            ->first();

        $leagues = League::with('teams')->withCount('teams')->get();
        $teams = Team::with('league')->orderBy('name')->get();

        return view('portal.fixtures', compact('fixtures', 'nextMatch', 'leagues', 'teams'));
    }
}
