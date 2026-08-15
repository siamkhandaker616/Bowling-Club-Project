<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\BowlingScore;
use App\Models\Visitor;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $best = BowlingScore::max('score');

        if ($request->user()) {
            $visitor = Visitor::where('user_id', $request->user()->id)->first();
            if ($visitor) {
                $best = BowlingScore::where('visitor_id', $visitor->id)->max('score') ?? $best;
            }
        }

        $top = BowlingScore::query()
            ->orderByDesc('score')
            ->take(5)
            ->get();

        return view('game.game', compact('best', 'top'));
    }
}
