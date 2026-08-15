<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Leaderboard - The Tenth Frame</title>
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
            <span class="game-brand-tag">Leaderboard</span>
        </a>
        <nav class="game-nav">
            <a href="{{ route('game.index') }}">Play</a>
            @auth
                <a href="{{ route('dashboard') }}">{{ auth()->user()->name }}</a>
            @else
                <a href="{{ route('login') }}">Sign in</a>
            @endauth
        </nav>
    </header>

    <main class="game-main game-main-stack">
        <section class="game-panel">
            <h2 class="game-panel-head">Club High Scores</h2>

            @forelse($topScores as $score)
                <div class="game-row {{ $visitor && $score->visitor_id === $visitor->id ? 'game-row-me' : '' }}">
                    <span class="game-row-rank">{{ $loop->iteration }}</span>
                    <span class="game-row-name">
                        {{ $score->visitor?->name ?? 'Guest' }}
                        @if($score->is_high_score)
                            <span class="game-row-star" title="High score">&#9733;</span>
                        @endif
                    </span>
                    <span class="game-row-date">{{ $score->played_at->format('M j, Y') }}</span>
                    <span class="game-row-score">{{ $score->score }}</span>
                </div>
            @empty
                <div class="game-empty">No games bowled yet. <a href="{{ route('game.index') }}">Be the first.</a></div>
            @endforelse
        </section>

        @if($visitor)
            <section class="game-panel">
                <h2 class="game-panel-head">My Best Games</h2>
                @forelse($myScores as $score)
                    <div class="game-row">
                        <span class="game-row-rank">{{ $loop->iteration }}</span>
                        <span class="game-row-name">
                            {{ $score->played_at->format('M j') }}
                            @if($score->is_high_score)
                                <span class="game-row-star" title="High score">&#9733;</span>
                            @endif
                        </span>
                        <span class="game-row-date">{{ $score->score === $myScores->max('score') ? 'Personal best' : '' }}</span>
                        <span class="game-row-score">{{ $score->score }}</span>
                    </div>
                @empty
                    <div class="game-empty">No saved scores yet. <a href="{{ route('game.index') }}">Roll a game.</a></div>
                @endforelse
            </section>
        @else
            <div class="game-panel">
                <div class="game-empty">
                    <a href="{{ route('login') }}">Sign in</a> to keep your personal bests on the board. Guests bowl as "Guest".
                </div>
            </div>
        @endif

        <div class="game-cta">
            <a href="{{ route('game.index') }}" class="btn">Roll another game</a>
        </div>
    </main>

    <x-toast />
</body>
</html>
