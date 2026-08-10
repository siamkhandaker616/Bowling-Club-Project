<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Welcome, {{ $user->name }}</h2>
            <div style="display:flex;align-items:center;gap:10px;">
                <div class="ball-avatar ball-sm ball-coral"><div class="ball-holes"><span></span><span></span><span></span></div><span class="ball-initials">NR</span></div>
                <span class="badge-role member">Member</span>
            </div>
        </div>
    </x-slot>

    <div style="zoom:1.25;padding:0 1rem;max-width:900px;margin:0 auto;">

        <!-- Hero: My Next Booking -->
        <div style="margin-bottom:1.5rem;">
            <div class="dash-section-label" style="margin-bottom:8px;">My Next Booking</div>
            <div class="lane-perspective" style="border-radius:12px;">
                <div style="background:var(--sky-light);border-radius:10px;padding:1.25rem;display:grid;grid-template-columns:1fr auto;gap:1.5rem;align-items:center;border:2px solid var(--navy);">
                    <div>
                        <div style="font-family:var(--font-header);font-size:1.1rem;color:var(--navy);">Lane 5 · Saturday Night</div>
                        <div style="font-family:var(--font-sub);font-size:0.85rem;color:var(--slate);margin-top:4px;">Jul 26, 2026 · 7:00 PM - 9:00 PM</div>
                        <div style="display:flex;gap:8px;margin-top:10px;align-items:center;">
                            <div class="ball-avatar ball-sm ball-coral"><div class="ball-holes"><span></span></div><span class="ball-initials" style="font-size:0.45rem;">NR</span></div>
                            <div class="ball-avatar ball-sm ball-gold"><div class="ball-holes"><span></span></div><span class="ball-initials" style="font-size:0.45rem;">RZ</span></div>
                            <div class="ball-avatar ball-sm ball-sky"><div class="ball-holes"><span></span></div><span class="ball-initials" style="font-size:0.45rem;">ZR</span></div>
                            <div class="ball-avatar ball-sm ball-navy"><div class="ball-holes"><span></span></div><span class="ball-initials" style="font-size:0.45rem;">TN</span></div>
                            <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">4 players</span>
                        </div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-family:var(--font-display);font-size:2rem;color:var(--navy);line-height:1;">2</div>
                        <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">days away</div>
                        <a href="#" class="btn-lane primary" style="font-size:0.6rem;padding:6px 16px;margin-top:8px;display:block;">Manage</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Two columns: Scorecard + Events -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

            <!-- My Scorecard -->
            <div>
                <div class="dash-section-label" style="margin-bottom:8px;">My Scorecard</div>
                <div class="scorecard" style="width:100%;">
                    <div class="sc-frame"><div class="sc-num">1</div><div class="sc-rolls"><span class="sc-roll strike">X</span><span class="sc-roll"></span></div><div class="sc-total">20</div></div>
                    <div class="sc-frame"><div class="sc-num">2</div><div class="sc-rolls"><span class="sc-roll">7</span><span class="sc-roll spare">/</span></div><div class="sc-total">37</div></div>
                    <div class="sc-frame"><div class="sc-num">3</div><div class="sc-rolls"><span class="sc-roll">9</span><span class="sc-roll">-</span></div><div class="sc-total">46</div></div>
                    <div class="sc-frame"><div class="sc-num">4</div><div class="sc-rolls"><span class="sc-roll strike">X</span><span class="sc-roll"></span></div><div class="sc-total">66</div></div>
                    <div class="sc-frame"><div class="sc-num">5</div><div class="sc-rolls"><span class="sc-roll strike">X</span><span class="sc-roll"></span></div><div class="sc-total">86</div></div>
                    <div class="sc-frame"><div class="sc-num">6</div><div class="sc-rolls"><span class="sc-roll strike">X</span><span class="sc-roll"></span></div><div class="sc-total">106</div></div>
                    <div class="sc-frame"><div class="sc-num">7</div><div class="sc-rolls"><span class="sc-roll">8</span><span class="sc-roll spare">/</span></div><div class="sc-total">123</div></div>
                    <div class="sc-frame"><div class="sc-num">8</div><div class="sc-rolls"><span class="sc-roll">9</span><span class="sc-roll">-</span></div><div class="sc-total">132</div></div>
                    <div class="sc-frame"><div class="sc-num">9</div><div class="sc-rolls"><span class="sc-roll strike">X</span><span class="sc-roll"></span></div><div class="sc-total">152</div></div>
                    <div class="sc-frame"><div class="sc-num">10</div><div class="sc-rolls"><span class="sc-roll strike">X</span><span class="sc-roll strike">X</span><span class="sc-roll strike">X</span></div><div class="sc-total">182</div></div>
                </div>
                <!-- Stats row -->
                <div style="display:flex;gap:8px;margin-top:10px;">
                    <div style="flex:1;text-align:center;padding:8px;background:var(--pin-white);border:2px solid var(--navy);border-radius:8px;">
                        <div style="font-family:var(--font-mono);font-size:1.1rem;font-weight:700;color:var(--navy);">164</div>
                        <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">AVG</div>
                    </div>
                    <div style="flex:1;text-align:center;padding:8px;background:var(--pin-white);border:2px solid var(--navy);border-radius:8px;">
                        <div style="font-family:var(--font-mono);font-size:1.1rem;font-weight:700;color:var(--coral);">212</div>
                        <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">BEST</div>
                    </div>
                    <div style="flex:1;text-align:center;padding:8px;background:var(--pin-white);border:2px solid var(--navy);border-radius:8px;">
                        <div style="font-family:var(--font-mono);font-size:1.1rem;font-weight:700;color:var(--sky-dark);">47</div>
                        <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">GAMES</div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Events -->
            <div>
                <div class="dash-section-label" style="margin-bottom:8px;">Upcoming Events</div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;padding:12px;display:flex;gap:12px;align-items:center;">
                        <div style="min-width:48px;text-align:center;padding:6px 4px;background:var(--coral);border-radius:8px;">
                            <div style="font-family:var(--font-header);font-size:0.9rem;color:var(--pin-white);line-height:1;">26</div>
                            <div style="font-family:var(--font-mono);font-size:0.45rem;color:rgba(248,246,240,0.8);">JUL</div>
                        </div>
                        <div style="flex:1;">
                            <div style="font-family:var(--font-sub);font-size:0.75rem;">Saturday League Night</div>
                            <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">7:00 PM · Lanes 1-3</div>
                        </div>
                        <a href="#" class="btn-lane primary" style="font-size:0.55rem;padding:5px 12px;">RSVP</a>
                    </div>
                    <div style="background:var(--sky-light);border:2px solid var(--gold);border-radius:10px;padding:12px;display:flex;gap:12px;align-items:center;">
                        <div style="min-width:48px;text-align:center;padding:6px 4px;background:var(--gold);border-radius:8px;">
                            <div style="font-family:var(--font-header);font-size:0.9rem;color:var(--navy);line-height:1;">01</div>
                            <div style="font-family:var(--font-mono);font-size:0.45rem;color:var(--navy);">AUG</div>
                        </div>
                        <div style="flex:1;">
                            <div style="font-family:var(--font-sub);font-size:0.75rem;">Summer Social Mixer</div>
                            <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">6:30 PM · All Lanes</div>
                        </div>
                        <a href="#" class="btn-lane secondary" style="font-size:0.55rem;padding:5px 12px;">RSVP'd ✓</a>
                    </div>
                    <div style="background:var(--sky-light);border:2px solid var(--fog);border-radius:10px;padding:12px;display:flex;gap:12px;align-items:center;">
                        <div style="min-width:48px;text-align:center;padding:6px 4px;background:var(--mist);border-radius:8px;">
                            <div style="font-family:var(--font-header);font-size:0.9rem;color:var(--navy);line-height:1;">15</div>
                            <div style="font-family:var(--font-mono);font-size:0.45rem;color:var(--navy);">AUG</div>
                        </div>
                        <div style="flex:1;">
                            <div style="font-family:var(--font-sub);font-size:0.75rem;">Summer Showdown Tournament</div>
                            <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">All Day · Open Registration</div>
                        </div>
                        <a href="#" class="btn-lane primary" style="font-size:0.55rem;padding:5px 12px;">Enter</a>
                    </div>
                </div>
            </div>

        </div>

        <!-- Club News -->
        <div style="margin-top:1.5rem;">
            <div class="dash-section-label" style="margin-bottom:6px;">Club News</div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <div class="ball-return"><div class="mini-ball ball-gold"></div><span class="br-text">New high score alert! Rezwan bowled a 245 last night.</span></div>
                <div class="ball-return"><div class="mini-ball ball-sky"></div><span class="br-text">Lane 3 temporarily out of service for maintenance.</span></div>
            </div>
        </div>

    </div>
</x-app-layout>
