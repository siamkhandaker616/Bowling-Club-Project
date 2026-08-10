<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Steward Dashboard</h2>
            <div style="display:flex;align-items:center;gap:1rem;">
                <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--pin-white);">12 members checked in</span>
                <span class="badge-role steward">Steward</span>
            </div>
        </div>
    </x-slot>

    <div style="zoom:1.25;display:grid;grid-template-columns:220px 1fr 180px;gap:0;min-height:calc(100vh - 200px);">

        <!-- LEFT: Today's Schedule -->
        <div style="background:var(--sky-light);border-right:3px solid var(--navy);padding:1rem;display:flex;flex-direction:column;">
            <div class="dash-section-label" style="margin-bottom:8px;">Today's Schedule</div>
            <div style="display:flex;flex-direction:column;gap:2px;flex:1;">
                <div style="display:flex;align-items:stretch;gap:8px;">
                    <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);width:38px;text-align:right;padding-top:6px;">10:00</div>
                    <div style="flex:1;padding:6px 8px;background:var(--sky);border-radius:6px;border-left:3px solid var(--sky-dark);">
                        <div style="font-family:var(--font-sub);font-size:0.65rem;">Lane 2 · Rezwan</div>
                        <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">2hrs · 4 players</div>
                    </div>
                </div>
                <div style="display:flex;align-items:stretch;gap:8px;">
                    <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);width:38px;text-align:right;padding-top:6px;">11:00</div>
                    <div style="flex:1;padding:6px 8px;background:var(--gold-light);border-radius:6px;border-left:3px solid var(--gold);">
                        <div style="font-family:var(--font-sub);font-size:0.65rem;">League Match</div>
                        <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">Lanes 1-3 · 12 players</div>
                    </div>
                </div>
                <div style="display:flex;align-items:stretch;gap:8px;">
                    <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);width:38px;text-align:right;padding-top:6px;">14:00</div>
                    <div style="flex:1;padding:6px 8px;background:var(--sky);border-radius:6px;border-left:3px solid var(--sky-dark);">
                        <div style="font-family:var(--font-sub);font-size:0.65rem;">Lane 5 · Nusrat</div>
                        <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">1hr · 2 players</div>
                    </div>
                </div>
                <div style="display:flex;align-items:stretch;gap:8px;">
                    <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);width:38px;text-align:right;padding-top:6px;">16:00</div>
                    <div style="flex:1;padding:6px 8px;background:var(--coral-light);border-radius:6px;border-left:3px solid var(--coral);">
                        <div style="font-family:var(--font-sub);font-size:0.65rem;">Birthday Party</div>
                        <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">Lanes 4-6 · 15 guests</div>
                    </div>
                </div>
                <div style="display:flex;align-items:stretch;gap:8px;">
                    <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);width:38px;text-align:right;padding-top:6px;">18:00</div>
                    <div style="flex:1;padding:6px 8px;background:var(--sky);border-radius:6px;border-left:3px solid var(--sky-dark);">
                        <div style="font-family:var(--font-sub);font-size:0.65rem;">Lane 1 · Zarif</div>
                        <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">2hrs · 6 players</div>
                    </div>
                </div>
                <div style="display:flex;align-items:stretch;gap:8px;">
                    <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);width:38px;text-align:right;padding-top:6px;">20:00</div>
                    <div style="flex:1;padding:6px 8px;border:2px dashed var(--fog);border-radius:6px;">
                        <div style="font-family:var(--font-sub);font-size:0.65rem;color:var(--fog);">Available</div>
                    </div>
                </div>
            </div>
            <div style="margin-top:8px;padding-top:8px;border-top:2px solid var(--fog);text-align:center;">
                <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">5 bookings · 2 events · 2 open slots</span>
            </div>
        </div>

        <!-- CENTER: Member Directory + Events -->
        <div style="padding:1.25rem;overflow:hidden;">

            <!-- Member Directory -->
            <div style="margin-bottom:1.25rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <div class="dash-section-label" style="margin:0;">Member Directory</div>
                    <div style="display:flex;gap:6px;">
                        <input type="text" placeholder="Search members..." style="font-family:var(--font-body);font-size:0.7rem;padding:5px 10px;border:2px solid var(--navy);border-radius:20px;width:160px;background:var(--pin-white);">
                        <a href="#" class="btn-lane primary" style="font-size:0.6rem;padding:5px 12px;">+ New</a>
                    </div>
                </div>
                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;overflow:hidden;">
                    <div style="display:grid;grid-template-columns:40px 1fr 90px 80px 70px;padding:6px 12px;background:var(--navy);gap:8px;">
                        <span></span>
                        <span style="font-family:var(--font-mono);font-size:0.5rem;color:var(--fog);">MEMBER</span>
                        <span style="font-family:var(--font-mono);font-size:0.5rem;color:var(--fog);">ROLE</span>
                        <span style="font-family:var(--font-mono);font-size:0.5rem;color:var(--fog);">LAST VISIT</span>
                        <span style="font-family:var(--font-mono);font-size:0.5rem;color:var(--fog);">STATUS</span>
                    </div>
                    <div style="display:grid;grid-template-columns:40px 1fr 90px 80px 70px;padding:8px 12px;border-bottom:1px solid var(--fog);gap:8px;align-items:center;">
                        <div class="ball-avatar ball-sm ball-coral"><div class="ball-holes"><span></span><span></span><span></span></div><span class="ball-initials">NR</span></div>
                        <div><div style="font-family:var(--font-sub);font-size:0.7rem;">Nusrat Rahman</div><div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">nusrat@email.com</div></div>
                        <span class="badge-role member" style="font-size:0.5rem;width:fit-content;">Member</span>
                        <span style="font-family:var(--font-mono);font-size:0.6rem;">Today</span>
                        <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--sky-dark);">● Active</span>
                    </div>
                    <div style="display:grid;grid-template-columns:40px 1fr 90px 80px 70px;padding:8px 12px;border-bottom:1px solid var(--fog);gap:8px;align-items:center;">
                        <div class="ball-avatar ball-sm ball-gold"><div class="ball-holes"><span></span><span></span><span></span></div><span class="ball-initials">RZ</span></div>
                        <div><div style="font-family:var(--font-sub);font-size:0.7rem;">Rezwan Kabir</div><div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">rezwan@email.com</div></div>
                        <span class="badge-role member" style="font-size:0.5rem;width:fit-content;">Member</span>
                        <span style="font-family:var(--font-mono);font-size:0.6rem;">Yesterday</span>
                        <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--sky-dark);">● Active</span>
                    </div>
                    <div style="display:grid;grid-template-columns:40px 1fr 90px 80px 70px;padding:8px 12px;border-bottom:1px solid var(--fog);gap:8px;align-items:center;">
                        <div class="ball-avatar ball-sm ball-sky"><div class="ball-holes"><span></span><span></span><span></span></div><span class="ball-initials">ZR</span></div>
                        <div><div style="font-family:var(--font-sub);font-size:0.7rem;">Zarif Ahmed</div><div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">zarif@email.com</div></div>
                        <span class="badge-role member" style="font-size:0.5rem;width:fit-content;">Member</span>
                        <span style="font-family:var(--font-mono);font-size:0.6rem;">3 days ago</span>
                        <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--fog);">○ Inactive</span>
                    </div>
                    <div style="display:grid;grid-template-columns:40px 1fr 90px 80px 70px;padding:8px 12px;gap:8px;align-items:center;">
                        <div class="ball-avatar ball-sm ball-navy"><div class="ball-holes"><span></span><span></span><span></span></div><span class="ball-initials">SK</span></div>
                        <div><div style="font-family:var(--font-sub);font-size:0.7rem;">Siam Khandaker</div><div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">siam@email.com</div></div>
                        <span class="badge-role manager" style="font-size:0.5rem;width:fit-content;">Manager</span>
                        <span style="font-family:var(--font-mono);font-size:0.6rem;">Today</span>
                        <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--sky-dark);">● Active</span>
                    </div>
                </div>
            </div>

            <!-- Events & Fixtures -->
            <div>
                <div class="dash-section-label" style="margin-bottom:8px;">Upcoming Events & Fixtures</div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;overflow:hidden;">
                        <div style="background:var(--coral);padding:6px 10px;"><div style="font-family:var(--font-header);font-size:0.65rem;color:var(--pin-white);">SAT LEAGUE</div></div>
                        <div style="padding:10px;">
                            <div style="font-family:var(--font-sub);font-size:0.75rem;">Saturday League Night</div>
                            <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">Every Sat · 7:00 PM</div>
                            <div style="display:flex;gap:4px;margin-top:6px;">
                                <div class="ball-avatar ball-sm ball-coral" style="width:20px;height:20px;"><div class="ball-holes"><span></span></div><span class="ball-initials" style="font-size:0.4rem;">+</span></div>
                                <div class="ball-avatar ball-sm ball-gold" style="width:20px;height:20px;"><div class="ball-holes"><span></span></div><span class="ball-initials" style="font-size:0.4rem;">+</span></div>
                                <div class="ball-avatar ball-sm ball-sky" style="width:20px;height:20px;"><div class="ball-holes"><span></span></div><span class="ball-initials" style="font-size:0.4rem;">+</span></div>
                                <span style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);align-self:center;">8 teams</span>
                            </div>
                        </div>
                    </div>
                    <div style="background:var(--sky-light);border:2px solid var(--gold);border-radius:10px;overflow:hidden;">
                        <div style="background:var(--gold);padding:6px 10px;"><div style="font-family:var(--font-header);font-size:0.65rem;color:var(--navy);">BIRTHDAY</div></div>
                        <div style="padding:10px;">
                            <div style="font-family:var(--font-sub);font-size:0.75rem;">Tasnim's Birthday Bash</div>
                            <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">Sat Jul 26 · 4:00 PM</div>
                            <div style="display:flex;gap:4px;margin-top:6px;">
                                <div class="ball-avatar ball-sm ball-navy" style="width:20px;height:20px;"><div class="ball-holes"><span></span></div><span class="ball-initials" style="font-size:0.4rem;">+</span></div>
                                <div class="ball-avatar ball-sm ball-pin" style="width:20px;height:20px;"><div class="ball-holes"><span></span></div><span class="ball-initials" style="font-size:0.4rem;color:var(--navy);text-shadow:none;">+</span></div>
                                <span style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);align-self:center;">15 RSVPs</span>
                            </div>
                        </div>
                    </div>
                    <div style="background:var(--sky-light);border:2px solid var(--fog);border-radius:10px;overflow:hidden;">
                        <div style="background:var(--mist);padding:6px 10px;"><div style="font-family:var(--font-header);font-size:0.65rem;color:var(--navy);">TOURNAMENT</div></div>
                        <div style="padding:10px;">
                            <div style="font-family:var(--font-sub);font-size:0.75rem;">Summer Showdown</div>
                            <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">Aug 15 · All Day</div>
                            <div style="display:flex;gap:4px;margin-top:6px;">
                                <span style="font-family:var(--font-mono);font-size:0.5rem;color:var(--fog);">Registration open</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT: Quick Actions -->
        <div style="background:var(--sky-light);border-left:3px solid var(--navy);padding:0.75rem;display:flex;flex-direction:column;gap:6px;align-items:center;">
            <div class="dash-section-label" style="margin-bottom:2px;width:100%;">Quick Actions</div>
            <a href="#" class="shoe-tag" style="width:70%;"><div class="st-shape" style="padding:0.5rem 0.5rem 1rem;"><div class="st-icon" style="font-size:1.2rem;margin:0.4rem 0 0.2rem;">&#127918;</div><h4 style="font-size:0.6rem;">Book Lane</h4></div></a>
            <a href="#" class="shoe-tag white" style="width:70%;"><div class="st-shape" style="padding:0.5rem 0.5rem 1rem;"><div class="st-icon" style="font-size:1.2rem;margin:0.4rem 0 0.2rem;">&#128100;</div><h4 style="font-size:0.6rem;">New Member</h4></div></a>
            <a href="#" class="shoe-tag coral" style="width:70%;"><div class="st-shape" style="padding:0.5rem 0.5rem 1rem;"><div class="st-icon" style="font-size:1.2rem;margin:0.4rem 0 0.2rem;">&#127881;</div><h4 style="font-size:0.6rem;">Create Event</h4></div></a>
            <div style="margin-top:auto;padding-top:8px;border-top:2px solid var(--fog);text-align:center;">
                <div class="ball-avatar ball-sm ball-sky" style="margin:0 auto;"><div class="ball-holes"><span></span><span></span><span></span></div><span class="ball-initials">MR</span></div>
                <div style="font-family:var(--font-sub);font-size:0.6rem;margin-top:4px;">Maya R.</div>
                <span class="badge-role steward" style="font-size:0.45rem;padding:2px 6px;">Steward</span>
            </div>
        </div>

    </div>
</x-app-layout>
