<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $event->title }} — The Tenth Frame</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body style="min-height:100vh;">

    @component('site.partials.core-header', ['activeRoute' => 'public.events'])
    @endcomponent

    <main style="padding:6rem 2rem 4rem;max-width:860px;margin:0 auto;">

        <a href="{{ route('public.events') }}" style="font-family:var(--font-sub);font-size:0.8rem;color:var(--slate);text-decoration:none;display:inline-block;margin-bottom:1.5rem;">&larr; Back to all events</a>

        <div style="background:var(--navy);border-radius:14px;padding:2rem 2rem 1.75rem;position:relative;overflow:hidden;margin-bottom:1.5rem;">
            <div style="position:absolute;top:-40px;right:-40px;width:220px;height:220px;border-radius:50%;background:radial-gradient(circle,rgba(212,168,76,0.18) 0%,transparent 70%);pointer-events:none;"></div>
            <div style="position:relative;z-index:1;">
                <div style="font-family:var(--font-mono);font-size:0.65rem;color:var(--gold);text-transform:uppercase;letter-spacing:2px;margin-bottom:0.5rem;">
                    {{ $isPast ? 'Event Recap' : ($event->date->isToday() ? 'Happening Today' : 'Upcoming Event') }}
                </div>
                <h1 style="font-family:var(--font-display);font-size:1.8rem;color:var(--pin-white);text-transform:uppercase;margin:0 0 0.5rem;">{{ $event->title }}</h1>
                <div style="font-family:var(--font-sub);font-size:0.9rem;color:var(--fog);">
                    {{ \Carbon\Carbon::parse($event->date->format('Y-m-d') . ' ' . $event->time->format('H:i'))->format('l, M j, Y — g:i A') }} &bull; {{ $event->venue ?: 'The Tenth Frame' }}
                </div>
                @if(! $isPast)
                <div style="display:flex;align-items:center;gap:8px;margin-top:1rem;">
                    <span style="width:9px;height:9px;border-radius:50%;background:var(--gold);animation:pub-pulse 1.2s ease-in-out infinite;"></span>
                    <span style="font-family:var(--font-mono);font-size:0.7rem;color:var(--gold);text-transform:uppercase;letter-spacing:1px;">Starts in <span id="pub-show-countdown">—</span></span>
                </div>
                @endif
            </div>
        </div>

        <div style="background:var(--pin-white);border:2px solid var(--navy);border-radius:12px;padding:2rem;margin-bottom:1.5rem;">
            <h2 style="font-family:var(--font-header);font-size:0.95rem;text-transform:uppercase;color:var(--navy);margin-bottom:0.75rem;">About</h2>
            <p style="font-family:var(--font-sub);font-size:0.9rem;color:var(--slate);line-height:1.7;margin:0;">{{ $event->description ?: 'No description yet — but trust us, the pins will fall.' }}</p>
        </div>

        <div style="background:var(--pin-white);border:2px solid var(--navy);border-radius:12px;padding:2rem;margin-bottom:1.5rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.6rem;flex-wrap:wrap;gap:0.5rem;">
                <h2 style="font-family:var(--font-header);font-size:0.95rem;text-transform:uppercase;color:var(--navy);margin:0;">Spots Remaining</h2>
                <span style="font-family:var(--font-mono);font-size:0.75rem;color:var(--navy);">
                    <span id="pub-show-count">{{ $event->current_rsvps }}</span> / {{ $event->max_capacity }}
                </span>
            </div>
            <div style="height:18px;border:3px solid var(--navy);border-radius:9px;overflow:hidden;background:var(--mist);">
                <div id="pub-show-bar" style="height:100%;width:{{ $event->max_capacity > 0 ? round(($event->current_rsvps / $event->max_capacity) * 100) : 0 }}%;background:{{ $event->isFull() ? 'var(--coral)' : 'var(--gold)' }};transition:width 0.5s ease;"></div>
            </div>
            <div style="margin-top:0.5rem;font-family:var(--font-sub);font-size:0.8rem;color:var(--slate);">
                {{ $event->isFull() ? 'The board is full — spots are gone.' : (($n = $event->remainingSpots()) === 1 ? '1 spot still on the board.' : "{$n} spots still on the board.") }}
            </div>
        </div>

        <div style="background:var(--pin-white);border:2px solid var(--navy);border-radius:12px;padding:2rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;margin-bottom:1.25rem;">
                <h2 style="font-family:var(--font-header);font-size:0.95rem;text-transform:uppercase;color:var(--navy);margin:0;">{{ (float) $event->price > 0 ? 'Reserve Your Spot — ৳ ' . number_format((float) $event->price, 0) : 'RSVP — It\'s Free' }}</h2>
                @if((float) $event->price > 0)
                    <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">Pay securely with your card or mobile wallet</span>
                @endif
            </div>

            <div id="pub-rsvp-message" style="display:none;background:var(--mist);border:2px solid var(--navy);border-radius:8px;padding:1rem 1.5rem;margin-bottom:1.25rem;font-family:var(--font-sub);font-size:0.85rem;color:var(--navy);"></div>

            @if($isPast)
                <div style="font-family:var(--font-sub);font-size:0.9rem;color:var(--slate);">This event has already rolled. Catch the next one on the <a href="{{ route('public.events') }}" style="color:var(--coral);">events board</a>.</div>
            @elseif($event->isFull())
                <div style="font-family:var(--font-sub);font-size:0.9rem;color:var(--slate);">At full capacity. Follow the board — cancellations free up spots.</div>
            @else
                <form id="pub-rsvp-form" method="POST" action="{{ route('public.events.rsvp', $event) }}" novalidate class="gutter-form" style="display:flex;flex-direction:column;gap:1rem;">
                    @csrf
                    <div class="gutter-field field">
                        <label class="label" for="visitor_name">Your Name <span class="req">*</span></label>
                        <div class="inp-wrap">
                            <input id="visitor_name" name="visitor_name" type="text" class="input" value="{{ old('visitor_name') }}" required maxlength="120" placeholder="e.g. Samina Chowdhury">
                            <span class="gutter-flag">&#10003;</span>
                        </div>
                        <div class="gutter-err" data-for="visitor_name">@error('visitor_name'){{ $message }}@else Name is required @enderror</div>
                    </div>
                    <div class="gutter-field field">
                        <label class="label" for="visitor_email">Email <span class="req">*</span></label>
                        <div class="inp-wrap">
                            <input id="visitor_email" name="visitor_email" type="email" class="input" value="{{ old('visitor_email') }}" required maxlength="255" placeholder="you@example.com">
                            <span class="gutter-flag">&#10003;</span>
                        </div>
                        <div class="gutter-err" data-for="visitor_email">@error('visitor_email'){{ $message }}@else Email is required @enderror</div>
                    </div>
                    <div class="gutter-field field">
                        <label class="label" for="visitor_phone">Phone (optional)</label>
                        <div class="inp-wrap">
                            <input id="visitor_phone" name="visitor_phone" type="text" class="input" value="{{ old('visitor_phone') }}" maxlength="30" placeholder="01XXXXXXXXX">
                            <span class="gutter-flag">&#10003;</span>
                        </div>
                        <div class="gutter-err">&nbsp;</div>
                    </div>

                    @if($errors->any())
                        <div style="background:var(--coral-light);border:2px solid var(--coral);border-radius:8px;padding:1rem 1.5rem;font-family:var(--font-sub);font-size:0.85rem;color:var(--coral-dark);">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="lane-stage">
                        <div class="pin-rack">
                            <div class="pin-row"><span class="pin"></span><span class="pin"></span><span class="pin"></span><span class="pin"></span></div>
                            <div class="pin-row"><span class="pin"></span><span class="pin"></span><span class="pin"></span></div>
                            <div class="pin-row"><span class="pin"></span><span class="pin"></span></div>
                            <div class="pin-row"><span class="pin"></span></div>
                        </div>
                        <span class="ball-dot"></span>
                    </div>

                    <button id="pub-rsvp-submit" type="submit" class="btn-lane primary" style="font-size:0.65rem;padding:7px 18px;align-self:flex-start;">{{ (float) $event->price > 0 ? 'Proceed to Payment' : 'Confirm RSVP' }}</button>
                </form>

                <div id="pub-rsvp-done" style="display:none;align-items:center;gap:12px;">
                    <span style="width:34px;height:34px;border-radius:50%;background:var(--gold);color:var(--navy);font-family:var(--font-header);display:flex;align-items:center;justify-content:center;">&#10003;</span>
                    <div>
                        <div style="font-family:var(--font-header);font-size:0.9rem;color:var(--navy);">You're on the list!</div>
                        <div id="pub-rsvp-done-text" style="font-family:var(--font-sub);font-size:0.8rem;color:var(--slate);"></div>
                    </div>
                </div>
            @endif
        </div>

    </main>

    @include('site.partials.core-footer')

    <script>
    (function() {
        var form = document.getElementById('pub-rsvp-form');

        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                var message = document.getElementById('pub-rsvp-message');
                var submit = document.getElementById('pub-rsvp-submit');
                var errors = document.querySelectorAll('.gutter-err[data-for]');

                message.style.display = 'none';
                message.textContent = '';
                errors.forEach(function(el) { el.textContent = ''; el.innerHTML = '&nbsp;'; });
                submit.disabled = true;
                submit.textContent = 'Booking...';

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: new FormData(form)
                })
                .then(function(response) {
                    return response.json().then(function(data) {
                        return { ok: response.ok, data: data };
                    });
                })
                .then(function(result) {
                    if (!result.ok) {
                        submit.disabled = false;
                        submit.textContent = '{{ (float) $event->price > 0 ? "Proceed to Payment" : "Confirm RSVP" }}';
                        if (result.data && result.data.errors) {
                            var keys = Object.keys(result.data.errors);
                            keys.forEach(function(key) {
                                var el = document.querySelector('.gutter-err[data-for="' + key + '"]');
                                if (el) { el.textContent = result.data.errors[key][0]; el.style.color = 'var(--coral)'; }
                            });
                        } else if (result.data && result.data.error) {
                            message.style.display = 'block';
                            message.textContent = result.data.error;
                        } else {
                            message.style.display = 'block';
                            message.textContent = 'The lanes hiccuped — give it another roll.';
                        }
                        return;
                    }

                    if (result.data && result.data.redirect_url) {
                        window.location = result.data.redirect_url;
                        return;
                    }

                    form.style.display = 'none';
                    var done = document.getElementById('pub-rsvp-done');
                    document.getElementById('pub-rsvp-done-text').textContent = result.data.message || 'See you on the lanes.';
                    done.style.display = 'flex';

                    var current = parseInt(document.getElementById('pub-show-count').textContent, 10) || 0;
                    var target = parseInt(result.data.current_rsvps, 10);
                    var bar = document.getElementById('pub-show-bar');
                    var max = parseInt(result.data.max_capacity, 10);

                    var step = Math.max(1, Math.ceil(Math.abs(target - current) / 12));
                    (function countUp() {
                        if (current < target) {
                            current = Math.min(current + step, target);
                            document.getElementById('pub-show-count').textContent = current;
                            if (max) bar.style.width = Math.round((current / max) * 100) + '%';
                            setTimeout(countUp, 40);
                        }
                    })();
                })
                .catch(function() {
                    submit.disabled = false;
                    submit.textContent = '{{ (float) $event->price > 0 ? "Proceed to Payment" : "Confirm RSVP" }}';
                    message.style.display = 'block';
                    message.textContent = 'Connection hiccup — give it another roll.';
                });
            });
        }

        @if(! $isPast)
        var end = new Date('{{ \Carbon\Carbon::parse($event->date->format("Y-m-d") . " " . $event->time->format("H:i"))->toIso8601String() }}').getTime();

        function pubShowTick() {
            var diff = end - new Date().getTime();
            var el = document.getElementById('pub-show-countdown');
            if (diff <= 0) { el.textContent = 'NOW'; return; }
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
        }

        pubShowTick();
        setInterval(pubShowTick, 1000);
        @endif
    })();
    </script>

    <style>
    @keyframes pub-pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
    @media (prefers-reduced-motion: reduce) {
        @keyframes pub-pulse { 0%, 100% { opacity: 1; } }
        #pub-show-bar { transition: none !important; }
        #pub-show-countdown { transition: none !important; }
    }
    </style>

    <x-toast />

</body>
</html>
