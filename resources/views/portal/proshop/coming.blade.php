<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pro Shop — The Tenth Frame</title>
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
            <a href="{{ route('public.events') }}" class="btn btn-ghost" style="padding:8px 20px;font-size:0.8rem;">Events</a>
            <a href="{{ route('public.fixtures') }}" class="btn btn-ghost" style="padding:8px 20px;font-size:0.8rem;">Fixtures</a>
            <a href="{{ route('public.stats') }}" class="btn btn-ghost" style="padding:8px 20px;font-size:0.8rem;">Stats</a>
            <a href="{{ route('public.touring') }}" class="btn btn-ghost" style="padding:8px 20px;font-size:0.8rem;">Touring</a>
            <a href="{{ route('public.proshop.index') }}" class="btn btn-coral" style="padding:8px 20px;font-size:0.8rem;">Pro Shop</a>
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn" style="padding:8px 24px;font-size:0.85rem;">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" style="font-family:var(--font-sub);color:var(--navy);text-decoration:none;">Sign In</a>
                @endauth
            @endif
        </nav>
    </header>

    <main style="padding:8rem 2rem 4rem;max-width:640px;margin:0 auto;text-align:center;">

        <div style="background:var(--pin-white);border:2px solid var(--navy);border-radius:14px;padding:3rem 2rem;box-shadow:var(--shadow-lg);">
            <div style="font-size:3.5rem;line-height:1;margin-bottom:0.75rem;">&#128730;</div>
            <h1 style="font-family:var(--font-display);font-size:1.6rem;text-transform:uppercase;color:var(--navy);margin:0 0 0.75rem;">Pro Shop — Opening Soon</h1>
            <p style="font-family:var(--font-sub);font-size:0.9rem;color:var(--slate);line-height:1.7;margin:0 0 1.5rem;">
                The shelves are being stocked. Balls, shoes, towels, and lane gear land here shortly.
            </p>
            <a href="{{ route('public.events') }}" class="btn btn-gold" style="padding:10px 22px;font-size:0.8rem;">Back to the Events Hub</a>
        </div>

    </main>

    <footer style="background:var(--navy);color:var(--fog);padding:3rem 2rem;text-align:center;margin-top:4rem;">
        <div class="ball-accent" style="width:28px;height:28px;margin:0 auto 1rem;"></div>
        <p style="font-family:var(--font-display);font-size:1.2rem;color:var(--pin-white);margin-bottom:0.5rem;">The Tenth Frame</p>
        <p style="font-family:var(--font-sub);font-size:0.85rem;color:var(--fog);">The Tenth Frame Bowling Club &copy; {{ date('Y') }} &bull; Strike fast, roll loud.</p>
    </footer>

    <x-toast />

</body>
</html>
