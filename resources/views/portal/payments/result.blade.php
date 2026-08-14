<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment — The Tenth Frame</title>
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
            <a href="{{ route('public.events') }}" style="font-family:var(--font-sub);color:var(--navy);text-decoration:none;">Events</a>
            <a href="{{ route('public.fixtures') }}" style="font-family:var(--font-sub);color:var(--navy);text-decoration:none;">Fixtures</a>
            <a href="{{ route('public.stats') }}" style="font-family:var(--font-sub);color:var(--navy);text-decoration:none;">Stats</a>
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

    <main style="padding:8rem 2rem 4rem;max-width:560px;margin:0 auto;">

        @php
            $rsvp = $payment->payable;
            $event = $rsvp?->event;
            $headline = match ($status) {
                'success' => $payment->isSuccessful() ? 'Payment Confirmed!' : 'Payment Underway',
                'fail' => 'Payment Didn\'t Land',
                default => 'Payment Cancelled',
            };
            $emoji = match ($status) {
                'success' => $payment->isSuccessful() ? '&#127919;' : '&#8987;',
                'fail' => '&#127922;',
                default => '&#128477;',
            };
            $tone = match ($status) {
                'success' => $payment->isSuccessful() ? 'var(--gold)' : 'var(--sky-dark)',
                'fail' => 'var(--coral)',
                default => 'var(--fog)',
            };
            $copy = match ($status) {
                'success' => $payment->isSuccessful()
                    ? 'Your spot is locked in — the club secretary has been notified. See you on the lanes.'
                    : 'The bank is still settling — we\'ll confirm the moment the IPN lands. No action needed.',
                'fail' => 'The payment gateway declined the request. Your RSVP is held, not lost — you can retry.',
                default => 'The payment was cancelled before completion. Your RSVP is still open if you want to roll again.',
            };
        @endphp

        <div style="background:var(--pin-white);border:2px solid var(--navy);border-radius:14px;padding:2.5rem 2rem;text-align:center;box-shadow:var(--shadow-lg);">
            <div style="font-size:3.5rem;line-height:1;margin-bottom:0.75rem;">{!! $emoji !!}</div>
            <h1 style="font-family:var(--font-display);font-size:1.4rem;text-transform:uppercase;color:var(--navy);margin:0 0 0.75rem;">{{ $headline }}</h1>
            <p style="font-family:var(--font-sub);font-size:0.9rem;color:var(--slate);line-height:1.7;margin:0 0 1.5rem;">{{ $copy }}</p>

            <div style="background:var(--mist);border-radius:10px;padding:1.25rem 1.5rem;text-align:left;margin-bottom:1.5rem;">
                <div style="display:flex;justify-content:space-between;font-family:var(--font-sub);font-size:0.8rem;color:var(--navy);padding:4px 0;">
                    <span style="color:var(--slate);">Event</span>
                    <span style="font-weight:600;">{{ $event?->title ?? '—' }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-family:var(--font-sub);font-size:0.8rem;color:var(--navy);padding:4px 0;">
                    <span style="color:var(--slate);">Amount</span>
                    <span style="font-weight:600;">BDT {{ number_format((float) $payment->amount, 0) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-family:var(--font-sub);font-size:0.8rem;color:var(--navy);padding:4px 0;">
                    <span style="color:var(--slate);">Status</span>
                    <span style="font-weight:600;color:{{ $tone }};text-transform:uppercase;">{{ $payment->status }}</span>
                </div>
                @if($payment->transaction_id)
                <div style="display:flex;justify-content:space-between;font-family:var(--font-sub);font-size:0.8rem;color:var(--navy);padding:4px 0;">
                    <span style="color:var(--slate);">Transaction</span>
                    <span style="font-family:var(--font-mono);font-size:0.7rem;">{{ $payment->transaction_id }}</span>
                </div>
                @endif
            </div>

            <div style="display:flex;gap:0.75rem;justify-content:center;flex-wrap:wrap;">
                @if($status !== 'success')
                    <a href="{{ route('public.events.show', $event) }}" class="btn btn-gold" style="padding:10px 22px;font-size:0.8rem;">Retry RSVP</a>
                @else
                    <a href="{{ route('public.events.show', $event) }}" class="btn btn-gold" style="padding:10px 22px;font-size:0.8rem;">View Event</a>
                @endif
                <a href="{{ route('public.events') }}" class="btn" style="padding:10px 22px;font-size:0.8rem;">Events Hub</a>
            </div>
        </div>

    </main>

    <footer style="background:var(--navy);color:var(--fog);padding:3rem 2rem;text-align:center;margin-top:4rem;">
        <div class="ball-accent" style="width:28px;height:28px;margin:0 auto 1rem;"></div>
        <p style="font-family:var(--font-display);font-size:1.2rem;color:var(--pin-white);margin-bottom:0.5rem;">The Tenth Frame</p>
        <p style="font-family:var(--font-sub);font-size:0.85rem;color:var(--fog);">The Tenth Frame Bowling Club &copy; {{ date('Y') }} &bull; Strike fast, roll loud.</p>
    </footer>

</body>
</html>
