<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'The Tenth Frame Bowling') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body style="min-height: 100vh;">

        <header style="position: fixed; top: 0; left: 0; right: 0; z-index: 52; background: rgba(245, 248, 250, 0.95); backdrop-filter: blur(8px); border-bottom: 3px solid var(--navy); padding: 0.75rem 2rem; display: flex; align-items: center; justify-content: space-between;">
            <a href="/" style="text-decoration: none; display: flex; align-items: center; gap: 10px;">
                <div class="ball-accent" style="width: 32px; height: 32px;"></div>
                <span style="font-family: var(--font-display); font-size: 1.3rem; color: var(--navy); text-transform: uppercase;">The Tenth Frame</span>
            </a>
            <nav style="display: flex; align-items: center; gap: 1.5rem;">
                @if (Route::has('login'))
                    @auth
                        @if(Auth::user()->role === 'admin')
                            <a href="{{ route('site.announcements.index') }}" style="font-family: var(--font-sub); color: var(--slate); text-decoration: none; font-size: 0.85rem;">Manage Announcements</a>
                        @endif
                        <a href="{{ route('site.facility-map') }}" style="font-family: var(--font-sub); color: var(--slate); text-decoration: none; font-size: 0.85rem;">Facility Map</a>
                        <a href="{{ url('/dashboard') }}" class="btn" style="padding: 8px 24px; font-size: 0.85rem;">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" style="font-family: var(--font-sub); color: var(--navy); text-decoration: none; padding: 6px 14px; border-radius: 50px; transition: background 0.15s, color 0.15s;" onmouseover="this.style.background='var(--mist)'; this.style.color='var(--sky-dark)'" onmouseout="this.style.background=''; this.style.color='var(--navy)'">Sign In</a>
                        <a href="{{ route('public.fixtures') }}" class="btn btn-gold" style="padding: 8px 20px; font-size: 0.8rem;">Fixtures</a>
                        <a href="{{ route('site.facility-map') }}" style="font-family: var(--font-sub); color: var(--slate); text-decoration: none; font-size: 0.85rem;">Facility Map</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn" style="padding: 8px 24px; font-size: 0.85rem;">Join the Club</a>
                        @endif
                    @endauth
                @endif
            </nav>
        </header>

        @php
            $tickerAnnouncements = \App\Models\Announcement::where('is_active', true)
                ->orderByRaw("priority = 'urgent' DESC")
                ->orderByDesc('published_at')
                ->limit(5)
                ->get();
        @endphp

        @if($tickerAnnouncements->count())
        <div style="background:var(--navy);border-bottom:2px solid var(--gold);padding:6px 0;overflow:hidden;position:fixed;top:62px;left:0;right:0;z-index:51;">
            <div class="pub-ticker-track" style="display:flex;gap:3rem;white-space:nowrap;animation:pub-ticker-scroll 30s linear infinite;width:max-content;">
                @foreach($tickerAnnouncements as $t)
                    <span style="font-family:var(--font-mono);font-size:0.7rem;text-transform:uppercase;letter-spacing:1.5px;color:{{ $t->priority === 'urgent' ? 'var(--coral-light)' : 'var(--gold-light)' }};">
                        @if($t->priority === 'urgent')<span style="color:var(--coral);font-weight:700;">[URGENT]</span> @endif
                        {{ $t->title }}
                    </span>
                @endforeach
                @foreach($tickerAnnouncements as $t)
                    <span style="font-family:var(--font-mono);font-size:0.7rem;text-transform:uppercase;letter-spacing:1.5px;color:{{ $t->priority === 'urgent' ? 'var(--coral-light)' : 'var(--gold-light)' }};">
                        @if($t->priority === 'urgent')<span style="color:var(--coral);font-weight:700;">[URGENT]</span> @endif
                        {{ $t->title }}
                    </span>
                @endforeach
            </div>
        </div>
        @endif

        <section style="min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 8rem 2rem 4rem; position: relative; overflow: hidden;">
            <div style="position: absolute; top: 20%; left: 10%; opacity: 0.08; transform: rotate(-15deg);">
                <div class="pin-accent" style="width: 60px; height: 120px; background: var(--navy);"></div>
            </div>
            <div style="position: absolute; top: 30%; right: 12%; opacity: 0.06; transform: rotate(10deg);">
                <div class="pin-accent" style="width: 50px; height: 100px; background: var(--navy);"></div>
            </div>
            <div style="position: absolute; bottom: 15%; left: 15%; opacity: 0.05;">
                <div class="ball-accent" style="width: 100px; height: 100px;"></div>
            </div>

            <div class="lane-stripe" style="width: 200px; margin-bottom: 2rem;"></div>

            <h1 style="font-family: var(--font-display); font-size: 4rem; text-transform: uppercase; letter-spacing: 4px; color: var(--navy); line-height: 1.1; margin-bottom: 0.5rem; animation: fadeSlideUp 0.8s ease-out;">
                The Tenth Frame
            </h1>
            <p style="font-family: var(--font-sub); font-size: 1.3rem; color: var(--slate); margin-bottom: 2rem; animation: fadeSlideUp 0.8s ease-out 0.15s both;">
                The Tenth Frame Bowling Club
            </p>
            <p style="max-width: 520px; font-size: 1.05rem; color: var(--slate); line-height: 1.7; margin-bottom: 2.5rem; animation: fadeSlideUp 0.8s ease-out 0.3s both;">
                Where every frame tells a story. Premium lanes, signature shakes, and a community that lives for the perfect strike.
            </p>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center; animation: fadeSlideUp 0.8s ease-out 0.45s both;">
                <a href="{{ Route::has('register') ? route('register') : '#' }}" class="btn">Join the Club</a>
                <a href="#features" class="btn btn-gold">Explore</a>
            </div>
        </section>

        @php
            $club = \App\Models\Club::first();
        @endphp
        @if($club)
        <section id="live-widgets" style="padding: 2rem 2rem 4rem; max-width: 1100px; margin: 0 auto;">
            <div class="lane-stripe" style="margin-bottom: 3rem;"></div>
            <h2 style="font-family: var(--font-header); text-align: center; text-transform: uppercase; font-size: 2rem; margin-bottom: 3rem;">Live at the Club</h2>

            <div style="display: grid; grid-template-columns: 1fr 340px; gap: 2rem; align-items: start;">

                <div class="pub-reveal" style="background: var(--pin-white); border: var(--border); border-radius: 12px; overflow: hidden; box-shadow: var(--shadow-md);">
                    <div style="background: var(--navy); padding: 0.75rem 1.25rem; display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-family: var(--font-header); font-size: 0.8rem; color: var(--pin-white); text-transform: uppercase; letter-spacing: 1px;">Lane Status</span>
                        <span id="pub-lane-updated" style="font-family: var(--font-mono); font-size: 0.6rem; color: var(--fog);">Loading...</span>
                    </div>
                    <div style="padding: 1.25rem;">
                        <div id="pub-lane-grid" style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 8px;">
                        </div>
                        <div style="display: flex; gap: 1.5rem; margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid var(--fog);">
                            <div style="display:flex;align-items:center;gap:6px;font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">
                                <span style="width:12px;height:12px;border-radius:3px;background:var(--sky);border:1.5px solid var(--sky-dark);"></span> Open
                            </div>
                            <div style="display:flex;align-items:center;gap:6px;font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">
                                <span style="width:12px;height:12px;border-radius:3px;background:var(--gold-light);border:1.5px solid var(--gold);"></span> Occupied
                            </div>
                            <div style="display:flex;align-items:center;gap:6px;font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">
                                <span style="width:12px;height:12px;border-radius:3px;background:var(--coral-light);border:1.5px solid var(--coral);"></span> Maintenance
                            </div>
                            <div style="display:flex;align-items:center;gap:6px;font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">
                                <span style="width:12px;height:12px;border-radius:3px;background:var(--navy);border:1.5px solid var(--navy);"></span> Reserved
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pub-reveal" style="background: var(--pin-white); border: var(--border); border-radius: 12px; overflow: hidden; box-shadow: var(--shadow-md);">
                    <div style="background: var(--navy); padding: 0.75rem 1.25rem;">
                        <span style="font-family: var(--font-header); font-size: 0.8rem; color: var(--pin-white); text-transform: uppercase; letter-spacing: 1px;">The Snack Bar</span>
                    </div>
                    <div style="padding: 1.25rem; text-align: center;">
                        <div id="pub-bar-status" style="margin-bottom: 1rem;"></div>
                        <div id="pub-bar-countdown" style="font-family: var(--font-mono); font-size: 0.8rem; color: var(--slate); margin-bottom: 1rem;"></div>
                        <div style="font-family: var(--font-mono); font-size: 0.65rem; color: var(--fog); text-transform: uppercase; letter-spacing: 1px;">
                            {{ \Carbon\Carbon::parse($club->bar_open_hours)->format('g:i A') }} &ndash; {{ \Carbon\Carbon::parse($club->bar_close_hours)->format('g:i A') }} daily
                        </div>
                    </div>
                </div>

            </div>
        </section>
        @endif

        <section id="features" style="padding: 5rem 2rem; max-width: 1100px; margin: 0 auto;">
            <div class="lane-stripe" style="margin-bottom: 3rem;"></div>
            <h2 style="font-family: var(--font-header); text-align: center; text-transform: uppercase; font-size: 2rem; margin-bottom: 3rem;">What We Offer</h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                <a href="{{ route('site.facility-map') }}" class="pub-reveal" style="background: var(--pin-white); border: var(--border); box-shadow: var(--shadow-md); padding: 2rem; transition: transform 0.15s, box-shadow 0.15s; text-decoration: none; color: inherit; display: block;" onmouseover="this.style.transform='translate(-3px,-3px)'; this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.transform=''; this.style.boxShadow='var(--shadow-md)'">
                    <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">&#128506;</div>
                    <h3 style="font-family: var(--font-sub); margin-bottom: 0.5rem; color: var(--navy);">Find Your Way Around</h3>
                    <p style="color: var(--slate); font-size: 0.9rem;">Interactive floor plan of the whole club. Hover any zone for its hours, click for the full rundown.</p>
                </a>

                <div class="pub-reveal" style="background: var(--pin-white); border: var(--border); box-shadow: var(--shadow-md); padding: 2rem; transition: transform 0.15s, box-shadow 0.15s;" onmouseover="this.style.transform='translate(-3px,-3px)'; this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.transform=''; this.style.boxShadow='var(--shadow-md)'">
                    <div class="ball-accent" style="width: 36px; height: 36px; margin-bottom: 1rem;"></div>
                    <h3 style="font-family: var(--font-sub); margin-bottom: 0.5rem;">12 Championship Lanes</h3>
                    <p style="color: var(--slate); font-size: 0.9rem;">Premium synthetic lanes, professionally oiled and maintained. Real-time availability tracking.</p>
                </div>

                <div class="pub-reveal" style="background: var(--pin-white); border: var(--border); box-shadow: var(--shadow-md); padding: 2rem; transition: transform 0.15s, box-shadow 0.15s;" onmouseover="this.style.transform='translate(-3px,-3px)'; this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.transform=''; this.style.boxShadow='var(--shadow-md)'">
                    <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">&#127923;</div>
                    <h3 style="font-family: var(--font-sub); margin-bottom: 0.5rem;">Social Events</h3>
                    <p style="color: var(--slate); font-size: 0.9rem;">Leagues, tournaments, and social nights. RSVP and reserve your spot before they fill up.</p>
                </div>

                <div class="pub-reveal" style="background: var(--pin-white); border: var(--border); box-shadow: var(--shadow-md); padding: 2rem; transition: transform 0.15s, box-shadow 0.15s;" onmouseover="this.style.transform='translate(-3px,-3px)'; this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.transform=''; this.style.boxShadow='var(--shadow-md)'">
                    <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">&#129380;</div>
                    <h3 style="font-family: var(--font-sub); margin-bottom: 0.5rem;">The Snack Bar</h3>
                    <p style="color: var(--slate); font-size: 0.9rem;">Fresh smoothies, specialty coffees, and game-day bites. Open daily from 10am.</p>
                </div>

                <div class="pub-reveal" style="background: var(--pin-white); border: var(--border); box-shadow: var(--shadow-md); padding: 2rem; transition: transform 0.15s, box-shadow 0.15s;" onmouseover="this.style.transform='translate(-3px,-3px)'; this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.transform=''; this.style.boxShadow='var(--shadow-md)'">
                    <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">&#127923;</div>
                    <h3 style="font-family: var(--font-sub); margin-bottom: 0.5rem;">Pro Shop</h3>
                    <p style="color: var(--slate); font-size: 0.9rem;">Custom ball drilling, premium equipment, and expert advice from our pro shop staff.</p>
                </div>

                <div class="pub-reveal" style="background: var(--pin-white); border: var(--border); box-shadow: var(--shadow-md); padding: 2rem; transition: transform 0.15s, box-shadow 0.15s;" onmouseover="this.style.transform='translate(-3px,-3px)'; this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.transform=''; this.style.boxShadow='var(--shadow-md)'">
                    <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">&#127942;</div>
                    <h3 style="font-family: var(--font-sub); margin-bottom: 0.5rem;">Touring Teams</h3>
                    <p style="color: var(--slate); font-size: 0.9rem;">Visiting from out of town? Book lanes, download welcome packs, and find nearby amenities.</p>
                </div>

                <div class="pub-reveal" style="background: var(--pin-white); border: var(--border); box-shadow: var(--shadow-md); padding: 2rem; transition: transform 0.15s, box-shadow 0.15s;" onmouseover="this.style.transform='translate(-3px,-3px)'; this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.transform=''; this.style.boxShadow='var(--shadow-md)'">
                    <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">&#127918;</div>
                    <h3 style="font-family: var(--font-sub); margin-bottom: 0.5rem;">Virtual Bowling</h3>
                    <p style="color: var(--slate); font-size: 0.9rem;">Can't make it in? Bowl from your browser. Top-down arcade bowling, high scores on the leaderboard.</p>
                </div>
            </div>
        </section>

        <footer style="background: var(--navy); color: var(--fog); padding: 3rem 2rem; text-align: center;">
            <div class="ball-accent" style="width: 28px; height: 28px; margin: 0 auto 1rem;"></div>
            <p style="font-family: var(--font-display); font-size: 1.2rem; color: var(--pin-white); margin-bottom: 0.5rem;">The Tenth Frame</p>
            <p style="font-family: var(--font-sub); font-size: 0.85rem; color: var(--fog);">The Tenth Frame Bowling Club &copy; {{ date('Y') }}</p>
        </footer>

        <script>
        (function() {
            @if($club)
            var barOpen = '{{ $club->bar_open_hours }}';
            var barClose = '{{ $club->bar_close_hours }}';

            function parseTime(t) {
                var p = t.split(':');
                return parseInt(p[0]) * 3600 + parseInt(p[1]) * 60 + parseInt(p[2] || 0);
            }

            var openSec = parseTime(barOpen);
            var closeSec = parseTime(barClose);

            function pad(n) { return n < 10 ? '0' + n : '' + n; }

            function updateBar() {
                var now = new Date();
                var secNow = now.getHours() * 3600 + now.getMinutes() * 60 + now.getSeconds();
                var isOpen = secNow >= openSec && secNow < closeSec;
                var statusEl = document.getElementById('pub-bar-status');
                var countdownEl = document.getElementById('pub-bar-countdown');

                if (isOpen) {
                    var secsLeft = closeSec - secNow;
                    var h = Math.floor(secsLeft / 3600);
                    var m = Math.floor((secsLeft % 3600) / 60);
                    var s = secsLeft % 60;
                    statusEl.innerHTML = '<div style="font-family:var(--font-header);font-size:1.1rem;text-transform:uppercase;letter-spacing:1px;color:var(--navy);padding:8px 20px;background:var(--gold-light);border:2px solid var(--gold);border-radius:50px;display:inline-block;">Open Now</div>';
                    countdownEl.innerHTML = pad(h) + ' : ' + pad(m) + ' : ' + pad(s) + ' <span style="font-size:0.6rem;text-transform:uppercase;letter-spacing:1px;">until close</span>';
                } else {
                    var secsUntil;
                    if (secNow < openSec) {
                        secsUntil = openSec - secNow;
                    } else {
                        secsUntil = (86400 - secNow) + openSec;
                    }
                    var h = Math.floor(secsUntil / 3600);
                    var m = Math.floor((secsUntil % 3600) / 60);
                    var s = secsUntil % 60;
                    statusEl.innerHTML = '<div style="font-family:var(--font-header);font-size:1.1rem;text-transform:uppercase;letter-spacing:1px;color:var(--slate);padding:8px 20px;background:var(--mist);border:2px solid var(--fog);border-radius:50px;display:inline-block;">Closed</div>';
                    countdownEl.innerHTML = pad(h) + ' : ' + pad(m) + ' : ' + pad(s) + ' <span style="font-size:0.6rem;text-transform:uppercase;letter-spacing:1px;">until open</span>';
                }
            }

            updateBar();
            setInterval(updateBar, 1000);
            @endif

            var statusColors = {
                'open': { bg: 'var(--sky)', border: 'var(--sky-dark)', text: 'var(--navy)' },
                'occupied': { bg: 'var(--gold-light)', border: 'var(--gold)', text: 'var(--navy)' },
                'maintenance': { bg: 'var(--coral-light)', border: 'var(--coral)', text: 'var(--navy)' },
                'reserved': { bg: 'var(--navy)', border: 'var(--navy)', text: 'var(--pin-white)' }
            };

            function fetchLanes() {
                fetch('{{ route("site.lanes.api") }}')
                    .then(function(r) { return r.json(); })
                    .then(function(lanes) {
                        var grid = document.getElementById('pub-lane-grid');
                        grid.innerHTML = '';
                        lanes.forEach(function(lane) {
                            var c = statusColors[lane.status] || statusColors.open;
                            var div = document.createElement('div');
                            div.style.cssText = 'display:flex;flex-direction:column;align-items:center;justify-content:center;padding:10px 4px 6px;border-radius:6px;border:2px solid ' + c.border + ';background:' + c.bg + ';transition:transform 0.15s;cursor:default;';
                            div.onmouseover = function() { div.style.transform = 'scale(1.08)'; };
                            div.onmouseout = function() { div.style.transform = ''; };
                            div.innerHTML = '<span style="font-family:var(--font-mono);font-size:0.6rem;font-weight:700;color:' + c.text + ';">L' + lane.lane_number + '</span>' +
                                '<span style="font-family:var(--font-mono);font-size:0.45rem;text-transform:uppercase;color:' + c.text + ';opacity:0.8;margin-top:2px;">' + lane.status + '</span>';
                            grid.appendChild(div);
                        });
                        var now = new Date();
                        document.getElementById('pub-lane-updated').textContent = 'Updated ' + pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
                    });
            }

            fetchLanes();
            setInterval(fetchLanes, 15000);

            var reveals = document.querySelectorAll('.pub-reveal');
            if (reveals.length && 'IntersectionObserver' in window) {
                var observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.1 });

                reveals.forEach(function(el) {
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(24px)';
                    el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
                    observer.observe(el);
                });
            }
        })();
        </script>

        <style>
        @keyframes pub-ticker-scroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        @media (max-width: 768px) {
            #live-widgets > div { grid-template-columns: 1fr !important; }
            #pub-lane-grid { grid-template-columns: repeat(4, 1fr) !important; }
        }
        @media (prefers-reduced-motion: reduce) {
            .pub-ticker-track { animation: none !important; }
            .pub-reveal { opacity: 1 !important; transform: none !important; transition: none !important; }
        }
        </style>
    </body>
</html>
