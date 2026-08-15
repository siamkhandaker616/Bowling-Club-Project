<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| LAYER C — MINI-GAME ROUTES
|--------------------------------------------------------------------------
| Owned by the Layer C (Virtual Bowling Mini-Game) build session.
| Feature 13: HTML5 Canvas bowling game, score saving, leaderboard.
|
| Rules:
|   - Add ALL mini-game routes here, never in routes/web.php.
|   - Suggested URL prefix: `/game` and `/leaderboard`.
|   - Do not reuse Breeze route names (see routes/public.php header).
|   - This file is loaded under the `web` middleware (see bootstrap/app.php).
*/

Route::get('/game', [App\Http\Controllers\Game\GameController::class, 'index'])
    ->name('game.index');

Route::post('/game/scores', [App\Http\Controllers\Game\ScoreController::class, 'store'])
    ->name('game.scores.store')
    ->middleware('throttle:60,1');

Route::get('/game/leaderboard', [App\Http\Controllers\Game\ScoreController::class, 'index'])
    ->name('game.leaderboard');
