<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Touring Teams — The Tenth Frame</title>
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
            <a href="{{ route('public.events') }}" style="font-family:var(--font-sub);color:var(--navy);text-decoration:none;">Events</a>
            <a href="{{ route('public.touring') }}" style="font-family:var(--font-sub);color:var(--gold);text-decoration:none;font-weight:600;">Touring</a>
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
            <h1 style="font-family:var(--font-display);font-size:2.2rem;text-transform:uppercase;color:var(--navy);margin-bottom:0.25rem;">Touring Teams Welcome Portal</h1>
            <p style="font-family:var(--font-sub);color:var(--slate);font-size:1rem;">Rolling into town? Book your visit and we'll have the lanes oiled and the welcome mat out.</p>
            <div class="lane-stripe" style="margin:1.5rem auto 0;max-width:400px;"></div>
        </div>

        <div style="display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,1fr);gap:1.5rem;align-items:start;">
            <div style="background:var(--pin-white);border:2px solid var(--navy);border-radius:12px;padding:2rem;">
                <h2 style="font-family:var(--font-header);font-size:1rem;text-transform:uppercase;color:var(--navy);margin-bottom:1.25rem;">Booking Request</h2>

                @if($errors->any())
                    <div style="background:var(--coral-light);border:2px solid var(--coral);border-radius:8px;padding:1rem 1.5rem;margin-bottom:1.5rem;font-family:var(--font-sub);font-size:0.85rem;color:var(--coral-dark);">
                        Gutter ball — a few fields need another roll before we can save your request.
                    </div>
                @endif

                <form method="POST" action="{{ route('public.touring.store') }}" style="display:flex;flex-direction:column;gap:1.1rem;">
                    @csrf

                    <div>
                        <label for="team_name" style="display:block;font-family:var(--font-sub);font-size:0.8rem;color:var(--slate);margin-bottom:5px;">Team Name *</label>
                        <input id="team_name" name="team_name" type="text" value="{{ old('team_name') }}" required maxlength="120" placeholder="e.g. Thunder Rollers"
                               style="width:100%;padding:10px 14px;border:2px solid var(--fog);border-radius:8px;font-family:var(--font-body);font-size:0.9rem;background:var(--cloud);color:var(--navy);outline:none;">
                        @error('team_name')<span style="font-size:0.75rem;color:var(--coral);margin-top:4px;display:block;">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label for="home_club" style="display:block;font-family:var(--font-sub);font-size:0.8rem;color:var(--slate);margin-bottom:5px;">Home Club</label>
                        <input id="home_club" name="home_club" type="text" value="{{ old('home_club') }}" maxlength="120" placeholder="e.g. Harbor Lanes"
                               style="width:100%;padding:10px 14px;border:2px solid var(--fog);border-radius:8px;font-family:var(--font-body);font-size:0.9rem;background:var(--cloud);color:var(--navy);outline:none;">
                        @error('home_club')<span style="font-size:0.75rem;color:var(--coral);margin-top:4px;display:block;">{{ $message }}</span>@enderror
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div>
                            <label for="arrival_date" style="display:block;font-family:var(--font-sub);font-size:0.8rem;color:var(--slate);margin-bottom:5px;">Arrival Date *</label>
                            <input id="arrival_date" name="arrival_date" type="date" value="{{ old('arrival_date') }}" required
                                   style="width:100%;padding:10px 14px;border:2px solid var(--fog);border-radius:8px;font-family:var(--font-mono);font-size:0.85rem;background:var(--cloud);color:var(--navy);outline:none;">
                            @error('arrival_date')<span style="font-size:0.75rem;color:var(--coral);margin-top:4px;display:block;">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label for="player_count" style="display:block;font-family:var(--font-sub);font-size:0.8rem;color:var(--slate);margin-bottom:5px;">Player Count *</label>
                            <input id="player_count" name="player_count" type="number" min="1" max="24" value="{{ old('player_count', 5) }}" required
                                   style="width:100%;padding:10px 14px;border:2px solid var(--fog);border-radius:8px;font-family:var(--font-mono);font-size:0.85rem;background:var(--cloud);color:var(--navy);outline:none;">
                            @error('player_count')<span style="font-size:0.75rem;color:var(--coral);margin-top:4px;display:block;">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="message" style="display:block;font-family:var(--font-sub);font-size:0.8rem;color:var(--slate);margin-bottom:5px;">Message to the Club Secretary</label>
                        <textarea id="message" name="message" rows="4" maxlength="2000" placeholder="Anything we should know? Gear needs, practice preferences, arrival time..."
                                  style="width:100%;padding:10px 14px;border:2px solid var(--fog);border-radius:8px;font-family:var(--font-body);font-size:0.9rem;background:var(--cloud);color:var(--navy);outline:none;resize:vertical;">{{ old('message') }}</textarea>
                        @error('message')<span style="font-size:0.75rem;color:var(--coral);margin-top:4px;display:block;">{{ $message }}</span>@enderror
                    </div>

                    <button type="submit" class="btn btn-gold" style="align-self:flex-start;">Send Request</button>
                </form>
            </div>

            <aside style="display:flex;flex-direction:column;gap:1.5rem;">
                <div style="background:var(--navy);border-radius:12px;padding:1.5rem;color:var(--pin-white);">
                    <h3 style="font-family:var(--font-header);font-size:0.85rem;text-transform:uppercase;color:var(--gold);margin-bottom:1rem;">What Your Team Gets</h3>
                    <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:0.6rem;">
                        <li style="font-family:var(--font-sub);font-size:0.85rem;display:flex;gap:10px;">
                            <span style="color:var(--gold);">&#9917;</span> Reserved lane block on arrival
                        </li>
                        <li style="font-family:var(--font-sub);font-size:0.85rem;display:flex;gap:10px;">
                            <span style="color:var(--gold);">&#9917;</span> Complimentary welcome drinks
                        </li>
                        <li style="font-family:var(--font-sub);font-size:0.85rem;display:flex;gap:10px;">
                            <span style="color:var(--gold);">&#9917;</span> Practice session with our pro coach
                        </li>
                        <li style="font-family:var(--font-sub);font-size:0.85rem;display:flex;gap:10px;">
                            <span style="color:var(--gold);">&#9917;</span> Printable welcome pack + directions
                        </li>
                    </ul>
                </div>

                <div style="background:var(--pin-white);border:2px solid var(--navy);border-radius:12px;padding:1.5rem;">
                    <h3 style="font-family:var(--font-header);font-size:0.85rem;text-transform:uppercase;color:var(--navy);margin-bottom:1rem;">Amenities</h3>
                    <div style="display:flex;flex-direction:column;gap:0.9rem;">
                        @foreach($amenities as $amenity)
                            <div>
                                <div style="font-family:var(--font-sub);font-size:0.85rem;color:var(--navy);font-weight:600;">{{ $amenity['name'] }}</div>
                                <div style="font-family:var(--font-sub);font-size:0.75rem;color:var(--slate);">{{ $amenity['note'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>

        <div style="margin-top:3rem;">
            <div class="lane-stripe" style="margin-bottom:1.5rem;"></div>
            <h2 style="font-family:var(--font-header);font-size:1.2rem;text-transform:uppercase;color:var(--navy);margin-bottom:0.25rem;">Getting Here</h2>
            <p style="font-family:var(--font-sub);font-size:0.85rem;color:var(--slate);margin-bottom:1.25rem;">{{ $club?->address }}</p>
            <div style="border:2px solid var(--navy);border-radius:12px;overflow:hidden;">
                <iframe src="https://www.google.com/maps?q={{ urlencode($club?->address ?? 'Dhaka') }}&output=embed" width="100%" height="360" style="border:0;display:block;" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Directions to The Tenth Frame"></iframe>
            </div>
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
