<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\BowlingScore;
use App\Models\Visitor;
use App\Services\Bowling\ScoringEngine;
use Illuminate\Http\Request;

class ScoreController extends Controller
{
    public function index(Request $request)
    {
        $topScores = BowlingScore::query()
            ->orderByDesc('score')
            ->orderBy('played_at')
            ->take(10)
            ->get();

        $myScores = collect();
        $visitor = null;

        if ($request->user()) {
            $visitor = Visitor::where('user_id', $request->user()->id)->first();

            if ($visitor) {
                $myScores = BowlingScore::query()
                    ->where('visitor_id', $visitor->id)
                    ->orderByDesc('score')
                    ->take(5)
                    ->get();
            }
        }

        return view('game.leaderboard', compact('topScores', 'myScores', 'visitor'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'score' => ['required', 'integer', 'min:0', 'max:300'],
            'frames_data' => ['required', 'array', 'min:10', 'max:10'],
        ]);

        $check = ScoringEngine::validate($data['frames_data']);

        if ($check !== true) {
            return response()->json(['error' => $check], 422);
        }

        $total = ScoringEngine::total($data['frames_data']);

        if ($total !== (int) $data['score']) {
            return response()->json(['error' => 'score does not match frames_data'], 422);
        }

        $visitorId = null;
        $isHigh = false;

        if ($user = $request->user()) {
            $visitor = Visitor::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'tier' => 'regular',
                    'reputation_score' => 50,
                ]
            );

            $visitorId = $visitor->id;
            $previousBest = BowlingScore::where('visitor_id', $visitorId)->max('score');
            $isHigh = $previousBest === null || $total > $previousBest;
        } else {
            $globalBest = BowlingScore::max('score');
            $isHigh = $globalBest === null || $total > $globalBest;
        }

        $score = BowlingScore::create([
            'visitor_id' => $visitorId,
            'score' => $total,
            'frames_data' => $data['frames_data'],
            'played_at' => now(),
            'is_high_score' => $isHigh,
        ]);

        return response()->json([
            'ok' => true,
            'high_score' => $isHigh,
            'score_id' => $score->id,
            'redirect' => route('game.leaderboard'),
        ]);
    }
}
