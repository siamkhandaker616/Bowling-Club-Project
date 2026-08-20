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

    @component('site.partials.core-header', ['activeRoute' => 'public.touring'])
    @endcomponent

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

                <form method="POST" action="{{ route('public.touring.store') }}" novalidate class="gutter-form" style="display:flex;flex-direction:column;gap:1.1rem;">
                    @csrf

                    <div class="gutter-field">
                        <label class="label" for="team_name">Team Name <span class="req">*</span></label>
                        <div class="inp-wrap">
                            <input class="input" id="team_name" name="team_name" type="text" value="{{ old('team_name') }}" required maxlength="120" placeholder="e.g. Thunder Rollers">
                            <span class="gutter-flag">&#10003;</span>
                        </div>
                        <div class="gutter-err">Team name is required</div>
                    </div>

                    <div class="gutter-field">
                        <label class="label" for="home_club">Home Club</label>
                        <div class="inp-wrap">
                            <input class="input" id="home_club" name="home_club" type="text" value="{{ old('home_club') }}" maxlength="120" placeholder="e.g. Harbor Lanes">
                        </div>
                    </div>

                    <div class="gutter-field">
                        <label class="label" for="contact_email">Team Contact Email <span class="req">*</span></label>
                        <div class="inp-wrap">
                            <input class="input" id="contact_email" name="contact_email" type="email" value="{{ old('contact_email') }}" required maxlength="190" placeholder="e.g. captain@thunderrollers.com">
                            <span class="gutter-flag">&#10003;</span>
                        </div>
                        <div class="gutter-err">Valid email is required</div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="gutter-field">
                        <label class="label" for="arrival_date">Arrival Date <span class="req">*</span></label>
                        @php
                            $touringDate = now();

                            if (old('arrival_date')) {
                                try {
                                    $touringDate = \Carbon\Carbon::parse(old('arrival_date'));
                                } catch (\Throwable $e) {
                                    $touringDate = now();
                                }
                            }
                        @endphp
                        <div class="inp-wrap">
                            <input class="input" type="date" name="arrival_date" data-datepicker value="{{ $touringDate->toDateString() }}" required>
                        </div>
                        <div class="gutter-err">Arrival date is required</div>
                    </div>
                        <div class="gutter-field">
                            <label class="label" for="player_count">Player Count <span class="req">*</span></label>
                            <div class="inp-wrap">
                                <input class="input" id="player_count" name="player_count" type="number" min="1" max="24" value="{{ old('player_count', 5) }}" required data-stepper="edit">
                                <span class="gutter-flag">&#10003;</span>
                            </div>
                            <div class="gutter-err">Player count is required</div>
                        </div>
                    </div>

                    <div class="gutter-field">
                        <label class="label" for="message">Message to the Club Secretary</label>
                        <div class="inp-wrap">
                            <textarea class="input" id="message" name="message" rows="4" maxlength="2000" placeholder="Anything we should know? Gear needs, practice preferences, arrival time...">{{ old('message') }}</textarea>
                        </div>
                    </div>

                    <div class="lane-stage">
                        <div class="pin-rack">
                            <div class="pin-row"><span class="pin"></span><span class="pin"></span><span class="pin"></span><span class="pin"></span></div>
                            <div class="pin-row"><span class="pin"></span><span class="pin"></span><span class="pin"></span></div>
                            <div class="pin-row"><span class="pin"></span><span class="pin"></span></div>
                            <div class="pin-row"><span class="pin"></span></div>
                        </div>
                        <span class="ball-dot"></span>
                    </div>

                    <button type="submit" class="submit">Send Request &rarr;</button>
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

    @include('site.partials.core-footer')

    <x-toast />

    @include('sim.partials.fold-controls')

    <script src="/js/datepicker.js"></script>
</body>
</html>
