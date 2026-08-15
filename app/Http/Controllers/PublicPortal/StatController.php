<?php

namespace App\Http\Controllers\PublicPortal;

use App\Http\Controllers\Controller;
use App\Models\League;
use App\Models\TeamMember;
use Illuminate\View\View;

class StatController extends Controller
{
    public function index(): View
    {
        $leagues = League::with(['teams' => function ($query) {
            $query->with('members')
                ->orderByDesc('wins')
                ->orderByDesc('draws')
                ->orderBy('losses');
        }])->get();

        $leagues->each(function ($league) {
            $league->teams->each(function ($team) {
                $team->played = $team->wins + $team->losses + $team->draws;
                $team->points = ($team->wins * 2) + $team->draws;
                $team->win_rate = $team->played > 0 ? (int) round(($team->wins / $team->played) * 100) : 0;
                $team->w_pct = $team->played > 0 ? (int) round(($team->wins / $team->played) * 100) : 0;
                $team->l_pct = $team->played > 0 ? (int) round(($team->losses / $team->played) * 100) : 0;
                $team->d_pct = $team->played > 0 ? (int) round(($team->draws / $team->played) * 100) : 0;
                $team->top_member = $team->members->sortByDesc('average_score')->first();
            });
        });

        $spotlight = TeamMember::with(['team.league'])
            ->whereHas('team')
            ->orderByDesc('average_score')
            ->limit(8)
            ->get();

        $spotlightData = $spotlight->map(function ($member) {
            return [
                'name' => $member->name,
                'score' => $member->average_score,
                'team' => $member->team?->name,
                'league' => $member->team?->league?->name,
            ];
        })->values()->all();

        return view('portal.stats', compact('leagues', 'spotlight', 'spotlightData'));
    }
}
