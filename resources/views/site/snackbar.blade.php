<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>The Snack Bar - The Tenth Frame</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body style="min-height: 100vh;">

        @component('site.partials.core-header')
            <a href="/" class="btn btn-ghost" style="padding: 8px 20px; font-size: 0.8rem;">Home</a>
            <a href="{{ route('site.facility-map') }}" class="btn btn-ghost" style="padding: 8px 20px; font-size: 0.8rem;">Facility Map</a>
            @if(Auth::check() && Auth::user()->role === 'admin')
                <a href="{{ route('site.announcements.index') }}" class="btn btn-ghost" style="padding: 8px 20px; font-size: 0.8rem;">Manage Announcements</a>
            @endif
            @if(Auth::check() && Auth::user()->role === 'customer')
                <a href="{{ route('public.proshop.cart') }}" class="btn btn-ghost" style="padding: 8px 20px; font-size: 0.8rem; position: relative;">Bag
                    @if(($bagCount ?? 0) > 0)<span style="position:absolute;top:-8px;right:-8px;min-width:18px;height:18px;border-radius:50%;background:var(--gold);color:var(--navy);font-family:var(--font-mono);font-size:.6rem;display:flex;align-items:center;justify-content:center;padding:0 4px;font-weight:700;">{{ $bagCount }}</span>@endif
                </a>
            @endif
        @endcomponent

        <section style="padding: 8rem 2rem 3rem; text-align: center; position: relative; overflow: hidden;">
            <div style="position: absolute; top: 25%; left: 12%; opacity: 0.06; transform: rotate(-15deg);">
                <div class="pin-accent" style="width: 50px; height: 100px; background: var(--navy);"></div>
            </div>
            <div style="position: absolute; bottom: 20%; right: 12%; opacity: 0.05;">
                <div class="ball-accent" style="width: 80px; height: 80px;"></div>
            </div>

            <div class="lane-stripe" style="width: 200px; margin: 0 auto 2rem;"></div>
            <h1 style="font-family: var(--font-display); font-size: 3rem; text-transform: uppercase; letter-spacing: 4px; color: var(--navy); line-height: 1.1; margin-bottom: 0.5rem; animation: fadeSlideUp 0.8s ease-out;">
                The Snack Bar
            </h1>
            <p style="font-family: var(--font-sub); font-size: 1.15rem; color: var(--slate); max-width: 520px; margin: 0 auto 2rem; line-height: 1.7; animation: fadeSlideUp 0.8s ease-out 0.15s both;">
                Smoothies, specialty coffees, and game-day bites &mdash; all non-alcoholic, all between frames.
            </p>

            @if($club)
            <div id="pub-bar-status" style="margin-bottom: 1rem; animation: fadeSlideUp 0.8s ease-out 0.3s both;"></div>
            <div id="pub-bar-countdown" style="font-family: var(--font-mono); font-size: 0.85rem; color: var(--slate); margin-bottom: 0.75rem; animation: fadeSlideUp 0.8s ease-out 0.4s both;"></div>
            <div style="font-family: var(--font-mono); font-size: 0.7rem; color: var(--fog); text-transform: uppercase; letter-spacing: 1.5px; animation: fadeSlideUp 0.8s ease-out 0.5s both;">
                {{ \Carbon\Carbon::parse($club->bar_open_hours)->format('g:i A') }} &ndash; {{ \Carbon\Carbon::parse($club->bar_close_hours)->format('g:i A') }} daily
            </div>
            @endif
        </section>

        <section style="padding: 1rem 2rem 5rem; max-width: 1100px; margin: 0 auto;">
            <div class="lane-stripe" style="margin-bottom: 3rem;"></div>
            <h2 style="font-family: var(--font-header); text-align: center; text-transform: uppercase; font-size: 2rem; margin-bottom: 3rem;">Non-Alcoholic Menu</h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                @foreach($menu as $category => $items)
                <div class="pub-reveal" style="background: var(--pin-white); border: var(--border); border-radius: 12px; box-shadow: var(--shadow-md); overflow: hidden;">
                    <div style="background: var(--navy); padding: 0.75rem 1.25rem;">
                        <span style="font-family: var(--font-header); font-size: 0.8rem; color: var(--pin-white); text-transform: uppercase; letter-spacing: 1px;">{{ $category }}</span>
                    </div>
                    <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                        @foreach($items as $item)
                        <div>
                            <div style="font-family: var(--font-sub); font-size: 0.95rem; color: var(--navy); font-weight: 700;">{{ $item->name }}</div>
                            <div style="font-size: 0.82rem; color: var(--slate); line-height: 1.5; margin-top: 0.15rem;">{{ $item->description }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        @include('site.partials.core-footer', ['noMarginTop' => true])

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
                var open24 = openSec === closeSec;
                var isOpen = open24 || (openSec < closeSec ? (secNow >= openSec && secNow < closeSec) : (secNow >= openSec || secNow < closeSec));
                var statusEl = document.getElementById('pub-bar-status');
                var countdownEl = document.getElementById('pub-bar-countdown');

                if (isOpen) {
                    statusEl.innerHTML = '<div style="font-family:var(--font-header);font-size:1.1rem;text-transform:uppercase;letter-spacing:1px;color:var(--navy);padding:8px 20px;background:var(--gold-light);border:2px solid var(--gold);border-radius:50px;display:inline-block;">Open Now</div>';
                    if (open24) {
                        countdownEl.innerHTML = '<span style="font-size:0.6rem;text-transform:uppercase;letter-spacing:1px;">Open 24 hours</span>';
                    } else {
                        var secsLeft = (closeSec <= secNow) ? (closeSec + 86400 - secNow) : (closeSec - secNow);
                        var h = Math.floor(secsLeft / 3600);
                        var m = Math.floor((secsLeft % 3600) / 60);
                        var s = secsLeft % 60;
                        countdownEl.innerHTML = pad(h) + ' : ' + pad(m) + ' : ' + pad(s) + ' <span style="font-size:0.6rem;text-transform:uppercase;letter-spacing:1px;">until close</span>';
                    }
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
        @media (prefers-reduced-motion: reduce) {
            .pub-reveal { opacity: 1 !important; transform: none !important; transition: none !important; }
        }
        </style>
    </body>
</html>
