<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Season Statistics — The Tenth Frame</title>
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
            <a href="/" class="btn btn-ghost" style="padding:8px 20px;font-size:0.8rem;">Home</a>
            <a href="{{ route('public.fixtures') }}" class="btn btn-ghost" style="padding:8px 20px;font-size:0.8rem;">Fixtures</a>
            <a href="{{ route('public.stats') }}" class="btn btn-coral" style="padding:8px 20px;font-size:0.8rem;">Stats</a>
            <a href="{{ route('public.events') }}" class="btn btn-ghost" style="padding:8px 20px;font-size:0.8rem;">Events</a>
            <a href="{{ route('public.touring') }}" class="btn btn-ghost" style="padding:8px 20px;font-size:0.8rem;">Touring</a>
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn" style="padding:8px 24px;font-size:0.85rem;">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost" style="padding:8px 20px;font-size:0.8rem;">Sign In</a>
                @endauth
            @endif
        </nav>
    </header>

    <main style="padding:6rem 2rem 4rem;max-width:1100px;margin:0 auto;">

        <div style="text-align:center;margin-bottom:2.5rem;">
            <h1 style="font-family:var(--font-display);font-size:2.2rem;text-transform:uppercase;color:var(--navy);margin-bottom:0.25rem;">Season Statistics</h1>
            <p style="font-family:var(--font-sub);color:var(--slate);font-size:1rem;">Every pin counts — the numbers behind the standings, crunched straight off the scoreboard.</p>
            <div class="lane-stripe" style="margin:1.5rem auto 0;max-width:400px;"></div>
        </div>

        @if($spotlight->isNotEmpty())
        <section style="margin-bottom:3rem;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;text-transform:uppercase;color:var(--navy);margin-bottom:1rem;">Player Spotlight</h2>
            <div id="pub-spotlight" style="background:var(--navy);border-radius:14px;padding:2rem;position:relative;overflow:hidden;box-shadow:var(--shadow-lg);">
                <div style="position:absolute;top:-40px;right:-40px;width:220px;height:220px;border-radius:50%;background:radial-gradient(circle,rgba(212,168,76,0.18) 0%,transparent 70%);pointer-events:none;"></div>
                <div style="position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:2rem;flex-wrap:wrap;">
                    <div style="display:flex;align-items:center;gap:1.25rem;">
                        <div id="pub-spotlight-rank" style="width:64px;height:64px;border-radius:50%;background:var(--gold);color:var(--navy);font-family:var(--font-display);font-size:1.6rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;"></div>
                        <div>
                            <div id="pub-spotlight-name" style="font-family:var(--font-header);font-size:1.4rem;color:var(--pin-white);"></div>
                            <div id="pub-spotlight-team" style="font-family:var(--font-sub);font-size:0.85rem;color:var(--fog);"></div>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-family:var(--font-mono);font-size:0.65rem;color:var(--fog);text-transform:uppercase;letter-spacing:1px;">Average Score</div>
                        <div id="pub-spotlight-score" style="font-family:var(--font-mono);font-size:2.4rem;color:var(--gold);font-weight:700;line-height:1.1;"></div>
                    </div>
                </div>
                <div style="position:relative;z-index:1;margin-top:1.5rem;height:4px;border-radius:2px;background:rgba(255,255,255,0.12);overflow:hidden;">
                    <div id="pub-spotlight-progress" style="height:100%;width:20%;background:var(--gold);border-radius:2px;transition:width 0.4s ease;"></div>
                </div>
            </div>
        </section>
        @endif

        @forelse($leagues as $league)
        <section style="margin-bottom:3rem;">
            <div style="display:flex;align-items:baseline;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;margin-bottom:1rem;">
                <h2 style="font-family:var(--font-header);font-size:1.2rem;text-transform:uppercase;color:var(--navy);">{{ $league->name }}</h2>
                <span style="font-family:var(--font-mono);font-size:0.7rem;color:var(--slate);text-transform:uppercase;letter-spacing:1px;">{{ $league->season }}</span>
            </div>

            <div style="display:grid;grid-template-columns:1fr;gap:0.75rem;margin-bottom:1.5rem;">
                @foreach($league->teams as $team)
                <div style="background:var(--pin-white);border:2px solid var(--navy);border-radius:10px;padding:0.9rem 1.25rem;">
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;margin-bottom:0.5rem;">
                        <div>
                            <span style="font-family:var(--font-header);font-size:0.9rem;color:var(--navy);">{{ $team->name }}</span>
                            <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);margin-left:0.5rem;">{{ $team->wins }}-{{ $team->losses }}-{{ $team->draws }}</span>
                        </div>
                        <div style="font-family:var(--font-mono);font-size:0.75rem;color:var(--navy);">{{ $team->win_rate }}% <span style="color:var(--slate);">W</span> &bull; {{ $team->points }} pts</div>
                    </div>
                    <div style="display:flex;height:14px;border-radius:7px;overflow:hidden;border:2px solid var(--navy);">
                        <div style="width:{{ $team->w_pct }}%;background:var(--gold);transition:width 0.6s ease;"></div>
                        <div style="width:{{ $team->d_pct }}%;background:var(--sky);transition:width 0.6s ease;"></div>
                        <div style="width:{{ $team->l_pct }}%;background:var(--coral);transition:width 0.6s ease;"></div>
                    </div>
                </div>
                @endforeach
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;">
                @foreach($league->teams as $team)
                <div class="pub-stat-card" style="background:var(--pin-white);border:2px solid var(--navy);border-radius:10px;padding:1.25rem;position:relative;overflow:hidden;transition:transform 0.15s,box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <div style="position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--gold),var(--coral));"></div>
                    <div style="font-family:var(--font-header);font-size:0.95rem;color:var(--navy);margin-bottom:0.75rem;">{{ $team->name }}</div>
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.5rem;margin-bottom:1rem;">
                        <div style="text-align:center;background:var(--mist);border-radius:8px;padding:0.5rem;">
                            <div style="font-family:var(--font-mono);font-size:1.1rem;color:var(--gold);font-weight:700;">{{ $team->wins }}</div>
                            <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);text-transform:uppercase;letter-spacing:1px;">Wins</div>
                        </div>
                        <div style="text-align:center;background:var(--mist);border-radius:8px;padding:0.5rem;">
                            <div style="font-family:var(--font-mono);font-size:1.1rem;color:var(--coral);font-weight:700;">{{ $team->losses }}</div>
                            <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);text-transform:uppercase;letter-spacing:1px;">Losses</div>
                        </div>
                        <div style="text-align:center;background:var(--mist);border-radius:8px;padding:0.5rem;">
                            <div style="font-family:var(--font-mono);font-size:1.1rem;color:var(--sky-dark);font-weight:700;">{{ $team->draws }}</div>
                            <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);text-transform:uppercase;letter-spacing:1px;">Draws</div>
                        </div>
                        <div style="text-align:center;background:var(--mist);border-radius:8px;padding:0.5rem;">
                            <div style="font-family:var(--font-mono);font-size:1.1rem;color:var(--navy);font-weight:700;">{{ $team->points }}</div>
                            <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);text-transform:uppercase;letter-spacing:1px;">Pts</div>
                        </div>
                    </div>
                    @if($team->top_member)
                    <div style="font-family:var(--font-sub);font-size:0.75rem;color:var(--slate);">
                        Top scorer: <span style="color:var(--navy);font-weight:600;">{{ $team->top_member->name }}</span>
                        <span style="font-family:var(--font-mono);color:var(--gold);">({{ $team->top_member->average_score }})</span>
                    </div>
                    @else
                    <div style="font-family:var(--font-sub);font-size:0.75rem;color:var(--slate);">Roster listing soon.</div>
                    @endif
                </div>
                @endforeach
            </div>
        </section>
        @empty
            <div style="text-align:center;padding:4rem 2rem;background:var(--pin-white);border:2px solid var(--navy);border-radius:12px;">
                <div style="font-size:3rem;margin-bottom:1rem;">🎳</div>
                <h3 style="font-family:var(--font-header);color:var(--navy);margin-bottom:0.5rem;">No Scores On The Board Yet</h3>
                <p style="font-family:var(--font-sub);color:var(--slate);">Standings will appear once the leagues have rolled a few frames.</p>
            </div>
        @endforelse

    </main>

    <footer style="background:var(--navy);color:var(--fog);padding:3rem 2rem;text-align:center;margin-top:4rem;">
        <div class="ball-accent" style="width:28px;height:28px;margin:0 auto 1rem;"></div>
        <p style="font-family:var(--font-display);font-size:1.2rem;color:var(--pin-white);margin-bottom:0.5rem;">The Tenth Frame</p>
        <p style="font-family:var(--font-sub);font-size:0.85rem;color:var(--fog);">The Tenth Frame Bowling Club &copy; {{ date('Y') }} &bull; Strike fast, roll loud.</p>
    </footer>

    <script>
    (function() {
        var spotlightData = @json($spotlightData);

        spotlightData.forEach(function (item, index) {
            item.rank = index + 1;
        });

        function pubRenderSpotlight(item) {
            document.getElementById('pub-spotlight-rank').textContent = item.rank;
            document.getElementById('pub-spotlight-name').textContent = item.name;
            document.getElementById('pub-spotlight-team').textContent = item.team + (item.league ? ' — ' + item.league : '');
            document.getElementById('pub-spotlight-score').textContent = item.score;
            var progress = document.getElementById('pub-spotlight-progress');
            progress.style.transition = 'none';
            progress.style.width = '0%';
            requestAnimationFrame(function() {
                progress.style.transition = 'width 3.5s linear';
                progress.style.width = '100%';
            });
        }

        var spotlightEl = document.getElementById('pub-spotlight');
        if (spotlightEl && spotlightData.length) {
            var idx = 0;
            pubRenderSpotlight(spotlightData[0]);
            setInterval(function() {
                idx = (idx + 1) % spotlightData.length;
                spotlightEl.style.opacity = '0';
                spotlightEl.style.transition = 'opacity 0.25s ease';
                setTimeout(function() {
                    pubRenderSpotlight(spotlightData[idx]);
                    spotlightEl.style.opacity = '1';
                }, 260);
            }, 4200);
        }

        var reveals = document.querySelectorAll('.pub-stat-card');
        if (reveals.length && 'IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.05 });
            reveals.forEach(function(el, i) {
                el.style.opacity = '0';
                el.style.transform = 'translateY(16px)';
                el.style.transition = 'opacity 0.5s ease-out ' + (i * 0.06) + 's, transform 0.5s ease-out ' + (i * 0.06) + 's';
                observer.observe(el);
            });
        }
    })();
    </script>

    <style>
    @media (prefers-reduced-motion: reduce) {
        .pub-stat-card { opacity: 1 !important; transform: none !important; transition: none !important; }
        #pub-spotlight-progress { transition: none !important; }
    }
    </style>

</body>
</html>
