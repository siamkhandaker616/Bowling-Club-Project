<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Events — The Tenth Frame</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body style="min-height:100vh;">

    <header style="position:fixed;top:0;left:0;right:0;z-index:50;background:rgba(245,248,250,0.95);backdrop-filter:blur(8px);border-bottom:3px solid var(--navy);padding:0.75rem 2rem;display:flex;align-items:center;justify-content:space-between;">
        <a href="/" style="text-decoration:none;display:flex;align-items:center;gap:10px;">
            <div class="ball-accent" style="width:32px;height:32px;"></div>
            <span style="font-family:var(--font-display);font-size:1.3rem;color:var(--navy);text-transform:uppercase;">The Tenth Frame</span>
        </a>
        <nav style="display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap;">
            <a href="/" style="font-family:var(--font-sub);color:var(--navy);text-decoration:none;">Home</a>
            <a href="{{ route('public.fixtures') }}" style="font-family:var(--font-sub);color:var(--navy);text-decoration:none;">Fixtures</a>
            <a href="{{ route('public.stats') }}" style="font-family:var(--font-sub);color:var(--navy);text-decoration:none;">Stats</a>
            <a href="{{ route('public.events') }}" style="font-family:var(--font-sub);color:var(--gold);text-decoration:none;font-weight:600;">Events</a>
            <a href="{{ route('public.touring') }}" style="font-family:var(--font-sub);color:var(--navy);text-decoration:none;">Touring</a>
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn" style="padding:8px 24px;font-size:0.85rem;">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" style="font-family:var(--font-sub);color:var(--navy);text-decoration:none;">Sign In</a>
                @endauth
            @endif
        </nav>
    </header>

    <main style="padding:6rem 2rem 4rem;max-width:1100px;margin:0 auto;">

        <div style="text-align:center;margin-bottom:2.5rem;">
            <h1 style="font-family:var(--font-display);font-size:2.2rem;text-transform:uppercase;color:var(--navy);margin-bottom:0.25rem;">Events Hub</h1>
            <p style="font-family:var(--font-sub);color:var(--slate);font-size:1rem;">Tournaments, socials, and lane nights — grab a spot before the board fills up.</p>
            <div class="lane-stripe" style="margin:1.5rem auto 0;max-width:400px;"></div>
        </div>

        @if($events->isEmpty())
            <div style="text-align:center;padding:4rem 2rem;background:var(--pin-white);border:2px solid var(--navy);border-radius:12px;">
                <div style="font-size:3rem;margin-bottom:1rem;">🎳</div>
                <h3 style="font-family:var(--font-header);color:var(--navy);margin-bottom:0.5rem;">No Events On The Board</h3>
                <p style="font-family:var(--font-sub);color:var(--slate);">Check back soon — the next event is being rolled into place.</p>
            </div>
        @endif

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1.25rem;">
            @foreach($events as $event)
                @php
                    $full = $event->isFull();
                    $past = $event->date < now()->toDateString();
                    $today = $event->date->isToday();
                    $pct = $event->max_capacity > 0 ? (int) round(($event->current_rsvps / $event->max_capacity) * 100) : 0;
                    $start = \Carbon\Carbon::parse($event->date->format('Y-m-d') . ' ' . $event->time->format('H:i'));
                @endphp
                <div class="pub-event-card" style="background:var(--pin-white);border:2px solid var(--navy);border-radius:12px;overflow:hidden;display:flex;flex-direction:column;transition:transform 0.15s,box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <div style="background:var(--navy);padding:1rem 1.25rem;display:flex;align-items:center;justify-content:space-between;gap:0.75rem;">
                        <span style="font-family:var(--font-header);font-size:0.8rem;color:var(--pin-white);text-transform:uppercase;letter-spacing:0.5px;">{{ $event->title }}</span>
                        <span style="font-family:var(--font-mono);font-size:0.6rem;padding:3px 10px;border-radius:50px;background:{{ $past ? 'var(--fog)' : ($today ? 'var(--coral)' : 'var(--gold)') }};color:var(--navy);text-transform:uppercase;letter-spacing:1px;white-space:nowrap;">
                            {{ $past ? 'Ended' : ($today ? 'Today' : 'Upcoming') }}
                        </span>
                    </div>
                    <div style="padding:1.25rem;display:flex;flex-direction:column;gap:1rem;flex:1;">
                        <div>
                            <div style="font-family:var(--font-sub);font-size:0.85rem;color:var(--navy);">{{ $start->format('l, M j, Y') }} &bull; {{ $start->format('g:i A') }}</div>
                            <div style="font-family:var(--font-sub);font-size:0.8rem;color:var(--slate);">{{ $event->venue ?: 'The Tenth Frame' }}</div>
                        </div>

                        @if(! $past)
                        <div>
                            <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);text-transform:uppercase;letter-spacing:1px;margin-bottom:0.25rem;">Starts in</div>
                            <div class="pub-countdown" data-start="{{ $start->toIso8601String() }}" style="font-family:var(--font-mono);font-size:1.1rem;color:var(--gold);font-weight:700;">—</div>
                        </div>
                        @endif

                        <div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.4rem;">
                                <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;letter-spacing:1px;">{{ $full ? 'Full' : ($event->remainingSpots() . ' spots left') }}</span>
                                <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--navy);">{{ $event->current_rsvps }}/{{ $event->max_capacity }}</span>
                            </div>
                            <div style="height:12px;border:2px solid var(--navy);border-radius:6px;overflow:hidden;background:var(--mist);">
                                <div style="height:100%;width:{{ $pct }}%;background:{{ $full ? 'var(--coral)' : 'var(--gold)' }};transition:width 0.5s ease;"></div>
                            </div>
                        </div>

                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:auto;gap:0.75rem;">
                            <span style="font-family:var(--font-mono);font-size:0.7rem;padding:4px 12px;border:2px solid var(--navy);border-radius:50px;background:var(--cloud);color:var(--navy);">
                                {{ (float) $event->price > 0 ? 'BDT ' . number_format((float) $event->price, 0) : 'Free' }}
                            </span>
                            <a href="{{ route('public.events.show', $event) }}" class="btn {{ $full || $past ? '' : 'btn-gold' }}" style="padding:8px 20px;font-size:0.75rem;">
                                {{ $full ? 'Full' : ($past ? 'Recap' : 'RSVP') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </main>

    <footer style="background:var(--navy);color:var(--fog);padding:3rem 2rem;text-align:center;margin-top:4rem;">
        <div class="ball-accent" style="width:28px;height:28px;margin:0 auto 1rem;"></div>
        <p style="font-family:var(--font-display);font-size:1.2rem;color:var(--pin-white);margin-bottom:0.5rem;">The Tenth Frame</p>
        <p style="font-family:var(--font-sub);font-size:0.85rem;color:var(--fog);">The Tenth Frame Bowling Club &copy; {{ date('Y') }} &bull; Strike fast, roll loud.</p>
    </footer>

    <script>
    (function() {
        var countdowns = document.querySelectorAll('.pub-countdown');

        function pubTick() {
            var now = new Date().getTime();
            countdowns.forEach(function(el) {
                var end = new Date(el.dataset.start).getTime();
                var diff = end - now;
                if (diff <= 0) {
                    el.textContent = 'NOW';
                    return;
                }
                var d = Math.floor(diff / 86400000);
                var h = Math.floor((diff % 86400000) / 3600000);
                var m = Math.floor((diff % 3600000) / 60000);
                var s = Math.floor((diff % 60000) / 1000);
                var parts = [];
                if (d > 0) parts.push(d + 'd');
                parts.push(h + 'h');
                parts.push(m + 'm');
                parts.push(s + 's');
                el.textContent = parts.join(' ');
            });
        }

        pubTick();
        setInterval(pubTick, 1000);
    })();
    </script>

    <style>
    @media (prefers-reduced-motion: reduce) {
        .pub-event-card { transform: none !important; transition: none !important; }
        .pub-countdown { transition: none !important; }
    }
    </style>

    <x-toast />

</body>
</html>
