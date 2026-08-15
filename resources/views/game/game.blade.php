<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Virtual Bowling - The Tenth Frame</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <link rel="stylesheet" href="{{ asset('css/bowling/game.css') }}">
</head>
<body class="game-page">
    <header class="game-topbar">
        <a href="{{ route('home') }}" class="game-brand">
            <span class="game-brand-ball"></span>
            <span class="game-brand-name">The Tenth Frame</span>
            <span class="game-brand-tag">Virtual Bowling</span>
        </a>
        <nav class="game-nav">
            <a href="{{ route('game.leaderboard') }}">Leaderboard</a>
            @auth
                <a href="{{ route('dashboard') }}">{{ auth()->user()->name }}</a>
            @else
                <a href="{{ route('login') }}">Sign in to track scores</a>
            @endauth
        </nav>
    </header>

    <main class="game-main">
        <section class="game-board">
            <div class="game-board-frame">
                <canvas id="game-canvas" width="500" height="650">Your browser does not support the HTML5 canvas.</canvas>
                <div class="game-status" id="game-status">Aim &amp; throw</div>
            </div>
        </section>

        <aside class="game-side">
            <div class="game-panel">
                <h2 class="game-panel-head">Scorecard</h2>
                <div class="scorecard game-scorecard" id="scoreboard">
                    @for($i = 1; $i <= 10; $i++)
                        <div class="sc-frame" data-frame="{{ $i }}">
                            <div class="sc-num">{{ $i }}</div>
                            <div class="sc-rolls">
                                @for($r = 1; $r <= 3; $r++)
                                    <span class="sc-roll" data-frame="{{ $i }}" data-slot="{{ $r }}"></span>
                                @endfor
                            </div>
                            <div class="sc-total" data-frame="{{ $i }}" data-total></div>
                        </div>
                    @endfor
                </div>
            </div>

            <div class="game-hud">
                <div class="game-hud-cell">
                    <span class="game-hud-label">Frame</span>
                    <span class="game-hud-value" id="hud-frame">1</span>
                </div>
                <div class="game-hud-cell">
                    <span class="game-hud-label">Ball</span>
                    <span class="game-hud-value" id="hud-ball">1</span>
                </div>
                <div class="game-hud-cell">
                    <span class="game-hud-label">Pins</span>
                    <span class="game-hud-value" id="hud-pins">10</span>
                </div>
                <div class="game-hud-cell">
                    <span class="game-hud-label">Best</span>
                    <span class="game-hud-value" id="hud-best">{{ $best ?? '—' }}</span>
                </div>
            </div>

            <div class="game-panel">
                <h2 class="game-panel-head">How to play</h2>
                <ul class="game-help">
                    <li><strong>Left-click + drag</strong> to aim &mdash; pull back like a slingshot, then <strong>release</strong> to throw. Direction sets the line, pull length sets power.</li>
                    <li><strong>Right-click + drag</strong> to slide the ball sideways and choose your shooting position.</li>
                    <li><strong>Swipe left/right</strong> on the release to add hook (curve).</li>
                    <li>Or use <strong>arrow keys</strong> to aim (&#8592;/&#8594;) and set power (&#8593;/&#8595;), <strong>Space</strong> to throw.</li>
                    <li>Strikes and spares score bonus pins &mdash; a perfect game is <strong>300</strong>.</li>
                    <li>Scores save to the club leaderboard when you finish a game.</li>
                </ul>
            </div>
        </aside>
    </main>

    <div class="game-over" id="game-over" hidden>
        <div class="game-over-card">
            <h2 class="game-over-title">Game Over</h2>
            <div class="game-over-score" id="over-score">0</div>
            <div class="game-over-badge" id="over-badge" hidden>New personal best!</div>
            <div class="game-over-meta" id="over-meta">Final score &mdash; thanks for bowling.</div>
            <div class="game-over-actions">
                <button type="button" class="btn" id="over-again">Roll again</button>
                <a href="{{ route('game.leaderboard') }}" class="btn btn-ghost">View leaderboard</a>
            </div>
        </div>
    </div>

    @php
        $gameConfig = [
            'scoresUrl' => route('game.scores.store'),
            'leaderboardUrl' => route('game.leaderboard'),
            'best' => $best ?? 0,
            'loggedIn' => auth()->check(),
        ];
    @endphp
    <script>
        window.GAME_CONFIG = @json($gameConfig);
    </script>
    <script src="{{ asset('js/bowling/game.js') }}"></script>
</body>
</html>
