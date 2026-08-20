<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome Pack — {{ $touring->team_name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body style="min-height:100vh;">

    @component('site.partials.core-header', ['activeRoute' => 'public.touring'])
@endcomponent

<main style="padding:6rem 2rem 4rem;max-width:820px;margin:0 auto;">

        <div style="text-align:center;margin-bottom:2.5rem;">
            <h1 style="font-family:var(--font-display);font-size:2rem;text-transform:uppercase;color:var(--navy);margin-bottom:0.25rem;">Welcome to The Tenth Frame</h1>
            <p style="font-family:var(--font-sub);color:var(--slate);font-size:1rem;">{{ $touring->team_name }} &bull; Arriving {{ $touring->arrival_date->format('l, M j, Y') }}</p>
            <div class="lane-stripe" style="margin:1.5rem auto 0;max-width:400px;"></div>
        </div>

        <section style="background:var(--pin-white);border:2px solid var(--navy);border-radius:12px;padding:2rem;margin-bottom:1.5rem;">
            <h2 style="font-family:var(--font-header);font-size:1rem;text-transform:uppercase;color:var(--navy);margin-bottom:1.25rem;">Your Booking</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem 2rem;">
                <div>
                    <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);text-transform:uppercase;letter-spacing:1px;">Team</div>
                    <div style="font-family:var(--font-header);font-size:1rem;color:var(--navy);">{{ $touring->team_name }}</div>
                </div>
                <div>
                    <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);text-transform:uppercase;letter-spacing:1px;">Home Club</div>
                    <div style="font-family:var(--font-sub);font-size:0.95rem;color:var(--navy);">{{ $touring->home_club ?: '—' }}</div>
                </div>
                <div>
                    <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);text-transform:uppercase;letter-spacing:1px;">Arrival Date</div>
                    <div style="font-family:var(--font-sub);font-size:0.95rem;color:var(--navy);">{{ $touring->arrival_date->format('l, M j, Y') }}</div>
                </div>
                <div>
                    <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);text-transform:uppercase;letter-spacing:1px;">Players</div>
                    <div style="font-family:var(--font-sub);font-size:0.95rem;color:var(--navy);">{{ $touring->player_count }}</div>
                </div>
                @if($touring->message)
                <div style="grid-column:1 / -1;">
                    <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);text-transform:uppercase;letter-spacing:1px;">Message</div>
                    <div style="font-family:var(--font-sub);font-size:0.95rem;color:var(--navy);">{{ $touring->message }}</div>
                </div>
                @endif
            </div>
        </section>

        <section style="background:var(--pin-white);border:2px solid var(--navy);border-radius:12px;padding:2rem;margin-bottom:1.5rem;">
            <h2 style="font-family:var(--font-header);font-size:1rem;text-transform:uppercase;color:var(--navy);margin-bottom:1.25rem;">What Your Team Gets</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.9rem;">
                <div style="font-family:var(--font-sub);font-size:0.9rem;color:var(--navy);display:flex;gap:10px;"><span style="color:var(--gold);">&#9917;</span> Reserved lane block on arrival</div>
                <div style="font-family:var(--font-sub);font-size:0.9rem;color:var(--navy);display:flex;gap:10px;"><span style="color:var(--gold);">&#9917;</span> Complimentary welcome drinks</div>
                <div style="font-family:var(--font-sub);font-size:0.9rem;color:var(--navy);display:flex;gap:10px;"><span style="color:var(--gold);">&#9917;</span> Practice session with our pro coach</div>
                <div style="font-family:var(--font-sub);font-size:0.9rem;color:var(--navy);display:flex;gap:10px;"><span style="color:var(--gold);">&#9917;</span> League-match scheduling at your pace</div>
            </div>
        </section>

        <section style="background:var(--pin-white);border:2px solid var(--navy);border-radius:12px;padding:2rem;margin-bottom:1.5rem;">
            <h2 style="font-family:var(--font-header);font-size:1rem;text-transform:uppercase;color:var(--navy);margin-bottom:1.25rem;">Nearby Amenities</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.9rem;">
                @foreach($amenities as $amenity)
                    <div>
                        <div style="font-family:var(--font-sub);font-size:0.9rem;color:var(--navy);font-weight:600;">{{ $amenity['name'] }}</div>
                        <div style="font-family:var(--font-sub);font-size:0.8rem;color:var(--slate);">{{ $amenity['note'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        <section style="background:var(--navy);border-radius:12px;padding:2rem;color:var(--pin-white);">
            <h2 style="font-family:var(--font-header);font-size:1rem;text-transform:uppercase;color:var(--gold);margin-bottom:1rem;">Club & Directions</h2>
            <div style="font-family:var(--font-sub);font-size:0.9rem;line-height:1.8;color:var(--fog);">
                <div>The Tenth Frame &bull; {{ $club?->address }}</div>
                <div>{{ $club?->phone }} &bull; {{ $club?->email }}</div>
                <div style="margin-top:0.5rem;">From the main road: follow the bowling-ball signs to the parking lot, enter the pro shop lobby, and check in at the front desk. Lanes open at 10 AM.</div>
            </div>
        </section>

    </main>

    <footer style="background:var(--navy);color:var(--fog);padding:3rem 2rem;text-align:center;margin-top:4rem;">
        <div class="ball-accent" style="width:28px;height:28px;margin:0 auto 1rem;"></div>
        <p style="font-family:var(--font-display);font-size:1.2rem;color:var(--pin-white);margin-bottom:0.5rem;">The Tenth Frame</p>
        <p style="font-family:var(--font-sub);font-size:0.85rem;color:var(--fog);">The Tenth Frame Bowling Club &copy; {{ date('Y') }} &bull; Strike fast, roll loud.</p>
    </footer>

    <x-toast />

    <style>
    @media print {
        header, footer, .no-print { display: none !important; }
        body { background: #ffffff !important; }
        main { padding: 1rem !important; max-width: 100% !important; }
        section { break-inside: avoid; box-shadow: none !important; }
    }
    </style>

</body>
</html>
