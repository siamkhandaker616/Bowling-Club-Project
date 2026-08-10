<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Caretaker Dashboard</h2>
            <div style="display:flex;align-items:center;gap:1rem;">
                <div style="display:flex;gap:6px;">
                    <span style="font-family:var(--font-mono);font-size:0.6rem;padding:3px 8px;border-radius:4px;background:var(--coral);color:var(--pin-white);">⚠ L3 JAMMED</span>
                    <span style="font-family:var(--font-mono);font-size:0.6rem;padding:3px 8px;border-radius:4px;background:var(--gold);color:var(--navy);">⚡ L6 LOW OIL</span>
                </div>
                <span class="badge-role caretaker">Caretaker</span>
            </div>
        </div>
    </x-slot>

    <div style="zoom:1.25;display:grid;grid-template-columns:180px 1fr 220px;gap:0;min-height:calc(100vh - 200px);">

        <!-- LEFT: My Tasks (Pin Checklist) -->
        <div style="background:var(--sky-light);border-right:3px solid var(--navy);padding:1rem;display:flex;flex-direction:column;">
            <div class="dash-section-label" style="margin-bottom:8px;">My Tasks Today</div>
            <div style="flex:1;display:flex;flex-direction:column;gap:8px;">
                <div style="display:flex;align-items:center;gap:8px;padding:6px 8px;background:var(--sky);border-radius:6px;border-left:3px solid var(--sky-dark);">
                    <div class="pin standing" style="transform:scale(0.7);margin:-8px -4px;"><div class="pin-head"></div><div class="pin-neck"></div><div class="pin-body"></div></div>
                    <div style="flex:1;">
                        <div style="font-family:var(--font-sub);font-size:0.6rem;">Oil Lanes 1-4</div>
                        <div style="font-family:var(--font-mono);font-size:0.45rem;color:var(--slate);">Scheduled 8:00 AM</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;padding:6px 8px;background:var(--coral-light);border-radius:6px;border-left:3px solid var(--coral);">
                    <div class="pin knocked" style="transform:scale(0.7);margin:-8px -4px;"><div class="pin-head"></div><div class="pin-neck"></div><div class="pin-body"></div></div>
                    <div style="flex:1;">
                        <div style="font-family:var(--font-sub);font-size:0.6rem;">Fix Lane 3 Jam</div>
                        <div style="font-family:var(--font-mono);font-size:0.45rem;color:var(--coral-dark);">URGENT</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;padding:6px 8px;background:var(--pin-white);border-radius:6px;border:2px solid var(--fog);">
                    <div class="pin standing" style="transform:scale(0.7);margin:-8px -4px;"><div class="pin-head"></div><div class="pin-neck"></div><div class="pin-body"></div></div>
                    <div style="flex:1;">
                        <div style="font-family:var(--font-sub);font-size:0.6rem;">Restock Shoes</div>
                        <div style="font-family:var(--font-mono);font-size:0.45rem;color:var(--slate);">Due 12:00 PM</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;padding:6px 8px;background:var(--pin-white);border-radius:6px;border:2px solid var(--fog);">
                    <div class="pin standing" style="transform:scale(0.7);margin:-8px -4px;"><div class="pin-head"></div><div class="pin-neck"></div><div class="pin-body"></div></div>
                    <div style="flex:1;">
                        <div style="font-family:var(--font-sub);font-size:0.6rem;">Clean Bar Area</div>
                        <div style="font-family:var(--font-mono);font-size:0.45rem;color:var(--slate);">Due 2:00 PM</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;padding:6px 8px;background:var(--pin-white);border-radius:6px;border:2px solid var(--fog);">
                    <div class="pin standing" style="transform:scale(0.7);margin:-8px -4px;"><div class="pin-head"></div><div class="pin-neck"></div><div class="pin-body"></div></div>
                    <div style="flex:1;">
                        <div style="font-family:var(--font-sub);font-size:0.6rem;">Check Pin Inventory</div>
                        <div style="font-family:var(--font-mono);font-size:0.45rem;color:var(--slate);">Due 5:00 PM</div>
                    </div>
                </div>
            </div>
            <div style="margin-top:8px;padding-top:8px;border-top:2px solid var(--fog);text-align:center;">
                <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">2 done · 1 urgent · 3 pending</div>
            </div>
        </div>

        <!-- CENTER: Lane Inspection + Logbook -->
        <div style="padding:1.25rem;overflow:hidden;">
            <div class="dash-section-label" style="margin-bottom:8px;">Lane Inspection Dashboard</div>
            @php
                $laneData = [
                    ['pct' => 92, 'oil' => 78, 'serviced' => '2d', 'status' => '● 92% Good', 'bg' => 'var(--sky-light)', 'border' => 'var(--sky-dark)', 'color' => 'var(--sky-dark)'],
                    ['pct' => 85, 'oil' => 65, 'serviced' => '3d', 'status' => '● 85% Good', 'bg' => 'var(--sky-light)', 'border' => 'var(--sky-dark)', 'color' => 'var(--sky-dark)'],
                    ['pct' => 15, 'oil' => 0, 'serviced' => '', 'status' => '✖ 15% JAMMED', 'bg' => 'var(--coral-light)', 'border' => 'var(--coral)', 'color' => 'var(--coral-dark)'],
                    ['pct' => 88, 'oil' => 72, 'serviced' => '1d', 'status' => '● 88% Good', 'bg' => 'var(--sky-light)', 'border' => 'var(--sky-dark)', 'color' => 'var(--sky-dark)'],
                    ['pct' => 60, 'oil' => 30, 'serviced' => '', 'status' => '▲ 60% Fair', 'bg' => 'var(--sky-light)', 'border' => 'var(--gold)', 'color' => 'var(--gold)'],
                    ['pct' => 45, 'oil' => 12, 'serviced' => '', 'status' => '▲ 45% LOW OIL', 'bg' => 'var(--gold-light)', 'border' => 'var(--gold)', 'color' => 'var(--gold-dust)'],
                    ['pct' => 95, 'oil' => 90, 'serviced' => 'today', 'status' => '● 95% Excellent', 'bg' => 'var(--sky-light)', 'border' => 'var(--sky-dark)', 'color' => 'var(--sky-dark)'],
                    ['pct' => 78, 'oil' => 55, 'serviced' => '4d', 'status' => '● 78% Good', 'bg' => 'var(--sky-light)', 'border' => 'var(--sky-dark)', 'color' => 'var(--sky-dark)'],
                ];
            @endphp
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;">
                @for($i = 0; $i < min(8, $stats['total_lanes'] ?? 8); $i++)
                    @php $ld = $laneData[$i]; @endphp
                    <div style="background:{{ $ld['bg'] }};border:2px solid {{ $ld['border'] }};border-radius:8px;padding:10px;text-align:center;">
                        <div style="font-family:var(--font-header);font-size:0.7rem;">Lane {{ $i + 1 }}</div>
                        <div style="height:4px;background:var(--fog);border-radius:2px;margin:6px 0;overflow:hidden;"><div style="width:{{ $ld['pct'] }}%;height:100%;background:{{ $ld['color'] }};border-radius:2px;"></div></div>
                        <div style="font-family:var(--font-mono);font-size:0.55rem;color:{{ $ld['color'] }};">{{ $ld['status'] }}</div>
                        <div style="font-family:var(--font-mono);font-size:0.45rem;color:var(--slate);">@if($ld['pct'] < 20) Pinsetter failure @elseif($ld['oil'] > 0) Oil: {{ $ld['oil'] }}% · Serviced {{ $ld['serviced'] }} ago @else Needs oiling @endif</div>
                    </div>
                @endfor
            </div>

            <!-- Maintenance Logbook -->
            <div style="margin-top:1.25rem;">
                <div class="dash-section-label" style="margin-bottom:6px;">Maintenance Logbook</div>
                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;padding:12px;font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);line-height:1.8;">
                    <div><span style="color:var(--navy);font-weight:700;">08:15</span> · Oiled Lanes 1-4. Applied 2 coats standard pattern.</div>
                    <div><span style="color:var(--navy);font-weight:700;">09:30</span> · Lane 3 pinsetter jam detected. Cleared debris. Still malfunctioning — escalated.</div>
                    <div><span style="color:var(--navy);font-weight:700;">10:00</span> · Replaced 6 worn pins on Lane 7 from spare inventory.</div>
                    <div><span style="color:var(--fog);">Waiting for parts for Lane 3 pinsetter motor...</span></div>
                </div>
            </div>
        </div>

        <!-- RIGHT: Stock Check + Crew + Profile -->
        <div style="background:var(--sky-light);border-left:3px solid var(--navy);padding:1rem;display:flex;flex-direction:column;">
            <div class="dash-section-label" style="margin-bottom:8px;">Stock Check</div>
            <div style="display:flex;flex-direction:column;gap:10px;flex:1;">
                <div style="padding:8px;background:var(--mist);border-radius:8px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                        <span style="font-family:var(--font-sub);font-size:0.65rem;">&#128098; Shoes</span>
                        <span style="font-family:var(--font-mono);font-size:0.6rem;">42 / 50</span>
                    </div>
                    <div style="height:6px;background:var(--fog);border-radius:3px;overflow:hidden;margin-bottom:4px;"><div style="width:84%;height:100%;background:var(--sky-dark);border-radius:3px;"></div></div>
                    <div style="display:flex;gap:4px;">
                        <button style="flex:1;font-family:var(--font-mono);font-size:0.55rem;padding:3px;border:1px solid var(--navy);border-radius:4px;background:var(--pin-white);cursor:pointer;">- 5</button>
                        <button style="flex:1;font-family:var(--font-mono);font-size:0.55rem;padding:3px;border:1px solid var(--navy);border-radius:4px;background:var(--sky);cursor:pointer;">+ 5</button>
                    </div>
                </div>
                <div style="padding:8px;background:var(--coral-light);border-radius:8px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                        <span style="font-family:var(--font-sub);font-size:0.65rem;">&#128167; Lane Oil</span>
                        <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--coral-dark);">12% LOW</span>
                    </div>
                    <div style="height:6px;background:var(--fog);border-radius:3px;overflow:hidden;margin-bottom:4px;"><div style="width:12%;height:100%;background:var(--coral);border-radius:3px;"></div></div>
                    <div style="display:flex;gap:4px;">
                        <button style="flex:1;font-family:var(--font-mono);font-size:0.55rem;padding:3px;border:1px solid var(--coral);border-radius:4px;background:var(--pin-white);cursor:pointer;">Order</button>
                    </div>
                </div>
                <div style="padding:8px;background:var(--mist);border-radius:8px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                        <span style="font-family:var(--font-sub);font-size:0.65rem;">&#127936; Spare Pins</span>
                        <span style="font-family:var(--font-mono);font-size:0.6rem;">30 units</span>
                    </div>
                    <div style="height:6px;background:var(--fog);border-radius:3px;overflow:hidden;margin-bottom:4px;"><div style="width:60%;height:100%;background:var(--lane-wood);border-radius:3px;"></div></div>
                    <div style="display:flex;gap:4px;">
                        <button style="flex:1;font-family:var(--font-mono);font-size:0.55rem;padding:3px;border:1px solid var(--navy);border-radius:4px;background:var(--pin-white);cursor:pointer;">- 10</button>
                        <button style="flex:1;font-family:var(--font-mono);font-size:0.55rem;padding:3px;border:1px solid var(--navy);border-radius:4px;background:var(--sky);cursor:pointer;">+ 10</button>
                    </div>
                </div>
                <div style="padding:8px;background:var(--mist);border-radius:8px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                        <span style="font-family:var(--font-sub);font-size:0.65rem;">&#129532; Cleaning Supplies</span>
                        <span style="font-family:var(--font-mono);font-size:0.6rem;">Good</span>
                    </div>
                    <div style="height:6px;background:var(--fog);border-radius:3px;overflow:hidden;margin-bottom:4px;"><div style="width:70%;height:100%;background:var(--sky-dark);border-radius:3px;"></div></div>
                    <div style="display:flex;gap:4px;">
                        <button style="flex:1;font-family:var(--font-mono);font-size:0.55rem;padding:3px;border:1px solid var(--navy);border-radius:4px;background:var(--pin-white);cursor:pointer;">- 1</button>
                        <button style="flex:1;font-family:var(--font-mono);font-size:0.55rem;padding:3px;border:1px solid var(--navy);border-radius:4px;background:var(--sky);cursor:pointer;">+ 1</button>
                    </div>
                </div>
            </div>

            <!-- Crew Button -->
            <a href="#" style="display:flex;align-items:center;justify-content:center;gap:6px;padding:8px;background:var(--navy);border-radius:8px;text-decoration:none;cursor:pointer;margin-top:8px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                <span style="font-family:var(--font-sub);font-size:0.65rem;color:var(--pin-white);">My Crew</span>
                <span style="font-family:var(--font-mono);font-size:0.5rem;color:var(--gold);">4 →</span>
            </a>

            <!-- Profile -->
            <div style="margin-top:8px;padding-top:8px;border-top:2px solid var(--fog);text-align:center;">
                <div class="ball-avatar ball-sm ball-coral" style="margin:0 auto;"><div class="ball-holes"><span></span><span></span><span></span></div><span class="ball-initials">JM</span></div>
                <div style="font-family:var(--font-sub);font-size:0.6rem;margin-top:4px;">{{ ucfirst(Auth::user()->name) }}</div>
                <span class="badge-role caretaker" style="font-size:0.45rem;padding:2px 6px;">Caretaker</span>
                @if(isset($personalities) && $personalities->count())
                    <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);margin-top:2px;">{{ $personalities->pluck('trait_name')->implode(' · ') }}</div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>
