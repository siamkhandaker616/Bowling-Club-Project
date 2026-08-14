<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Caretaker Dashboard</h2>
            <div style="display:flex;align-items:center;gap:1rem;">
                <div style="display:flex;gap:6px;">
                    @foreach($lanes->where('status', 'maintenance')->take(2) as $lane)
                        <span style="font-family:var(--font-mono);font-size:0.6rem;padding:3px 8px;border-radius:4px;background:var(--coral);color:var(--pin-white);">&#9888; L{{ $lane->lane_number }} JAMMED</span>
                    @endforeach
                    @foreach($lanes->where('oil_level', '<', 20)->take(2) as $lane)
                        <span style="font-family:var(--font-mono);font-size:0.6rem;padding:3px 8px;border-radius:4px;background:var(--gold);color:var(--navy);">&#9889; L{{ $lane->lane_number }} LOW OIL</span>
                    @endforeach
                </div>
                <span class="badge-role caretaker">Caretaker</span>
            </div>
        </div>
    </x-slot>

    @if ($staff && ! $staff->is_active)
        <div style="zoom:1.25;margin:0.75rem 0;padding:0.75rem 1rem;border:2px solid var(--coral-dark);background:var(--coral-light);border-radius:10px;display:flex;align-items:center;justify-content:space-between;gap:1rem;">
            <div>
                <div style="font-family:var(--font-sub);font-size:0.75rem;color:var(--navy);font-weight:700;">You are no longer on the roster.</div>
                <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">Your employment ended with the club. You can reapply with a new identity.</div>
            </div>
            <a href="{{ route('reapply.index') }}" class="btn-lane primary" style="font-size:0.6rem;padding:6px 12px;text-decoration:none;white-space:nowrap;">Reapply Now</a>
        </div>
    @endif

    <div style="zoom:1.25;display:grid;grid-template-columns:180px 1fr 220px;gap:0;min-height:calc(100vh - 200px);">

        <!-- LEFT: My Tasks (Shifts) -->
        <div style="background:var(--sky-light);border-right:3px solid var(--navy);padding:1rem;display:flex;flex-direction:column;">
            <div class="dash-section-label" style="margin-bottom:8px;">My Tasks Today</div>
            <div style="flex:1;display:flex;flex-direction:column;gap:8px;">
                @forelse($myShifts as $shift)
                    @php
                        $isCompleted = $shift->status === 'completed';
                        $bg = $isCompleted ? 'var(--sky)' : ($shift->time_slot === 'morning' ? 'var(--sky)' : 'var(--pin-white)');
                        $border = $isCompleted ? 'var(--fog)' : ($shift->time_slot === 'morning' ? 'var(--sky-dark)' : 'var(--fog)');
                        $pinClass = $isCompleted ? 'pin knocked' : 'pin standing';
                    @endphp
                    <div style="display:flex;align-items:center;gap:8px;padding:6px 8px;background:{{ $bg }};border-radius:6px;border-left:3px solid {{ $border }};">
                        <div class="{{ $pinClass }}" style="transform:scale(0.7);margin:-8px -4px;"><div class="pin-head"></div><div class="pin-neck"></div><div class="pin-body"></div></div>
                        <div style="flex:1;">
                            <div style="font-family:var(--font-sub);font-size:0.6rem;">{{ ucfirst($shift->time_slot) }} · Lane {{ $shift->lane?->lane_number ?? '?' }}</div>
                            <div style="font-family:var(--font-mono);font-size:0.45rem;color:{{ $isCompleted ? 'var(--slate)' : 'var(--navy)' }};">{{ $isCompleted ? 'Done' : 'Scheduled' }}</div>
                        </div>
                    </div>
                @empty
                    <div style="padding:6px 8px;border:2px dashed var(--fog);border-radius:6px;text-align:center;">
                        <div style="font-family:var(--font-sub);font-size:0.6rem;color:var(--fog);">No shifts today</div>
                    </div>
                @endforelse
            </div>
            <div style="margin-top:8px;padding-top:8px;border-top:2px solid var(--fog);text-align:center;">
                <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">{{ $stats['completed_shifts'] }} done · {{ $myShifts->count() - $stats['completed_shifts'] }} pending</div>
            </div>
        </div>

        <!-- CENTER: Lane Inspection + Logbook -->
        <div style="padding:1.25rem;overflow:hidden;">
            <div class="dash-section-label" style="margin-bottom:8px;">Lane Inspection Dashboard</div>
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;">
                @foreach($lanes->take(8) as $lane)
                    @php
                        $oil = $lane->oil_level ?? 0;
                        if ($lane->status === 'maintenance') {
                            $status = '&#10008; JAMMED';
                            $bg = 'var(--coral-light)';
                            $border = 'var(--coral)';
                            $color = 'var(--coral-dark)';
                            $detail = 'Pinsetter failure';
                        } elseif ($oil < 20) {
                            $status = '&#9650; LOW OIL';
                            $bg = 'var(--gold-light)';
                            $border = 'var(--gold)';
                            $color = 'var(--gold)';
                            $detail = 'Needs oiling';
                        } elseif ($oil < 50) {
                            $status = '&#9650; ' . $oil . '% Fair';
                            $bg = 'var(--sky-light)';
                            $border = 'var(--gold)';
                            $color = 'var(--gold)';
                            $detail = 'Oil: ' . $oil . '%';
                        } else {
                            $status = '&#9679; ' . $oil . '% Good';
                            $bg = 'var(--sky-light)';
                            $border = 'var(--sky-dark)';
                            $color = 'var(--sky-dark)';
                            $detail = 'Oil: ' . $oil . '%';
                        }
                    @endphp
                    <div style="background:{{ $bg }};border:2px solid {{ $border }};border-radius:8px;padding:10px;text-align:center;">
                        <div style="font-family:var(--font-header);font-size:0.7rem;">Lane {{ $lane->lane_number }}</div>
                        <div style="height:4px;background:var(--fog);border-radius:2px;margin:6px 0;overflow:hidden;"><div style="width:{{ $oil }}%;height:100%;background:{{ $color }};border-radius:2px;"></div></div>
                        <div style="font-family:var(--font-mono);font-size:0.55rem;color:{{ $color }};">{!! $status !!}</div>
                        <div style="font-family:var(--font-mono);font-size:0.45rem;color:var(--slate);">{{ $detail }}</div>
                    </div>
                @endforeach
            </div>

            <!-- Confrontation Logbook -->
            @if($confrontations->count())
                <div style="margin-top:1.25rem;">
                    <div class="dash-section-label" style="margin-bottom:6px;">Recent Incidents</div>
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;padding:12px;font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);line-height:1.8;">
                        @foreach($confrontations as $conf)
                            <div>
                                <span style="color:var(--navy);font-weight:700;">{{ $conf->reporter->user->name ?? 'Staff' }}</span>
                                reported {{ $conf->accused->user->name ?? 'Staff' }}
                                @if($conf->manager_verdict) — Verdict: {{ $conf->manager_verdict }} @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- RIGHT: Stock Check + Crew + Profile -->
        <div style="background:var(--sky-light);border-left:3px solid var(--navy);padding:1rem;display:flex;flex-direction:column;">
            <div class="dash-section-label" style="margin-bottom:8px;">Stock Check</div>
            <div style="display:flex;flex-direction:column;gap:10px;flex:1;">
                @foreach($stock->take(6) as $item)
                    @php
                        $pct = $item->reorder_threshold > 0 ? min(100, ($item->quantity / ($item->reorder_threshold * 2)) * 100) : 100;
                        $isLow = $item->isLowStock();
                    @endphp
                    <div style="padding:8px;background:{{ $isLow ? 'var(--coral-light)' : 'var(--mist)' }};border-radius:8px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                            <span style="font-family:var(--font-sub);font-size:0.65rem;">{{ $item->name }}</span>
                            <span style="font-family:var(--font-mono);font-size:0.6rem;color:{{ $isLow ? 'var(--coral-dark)' : '' }};">{{ $item->quantity }} {{ $item->unit ?? '' }}</span>
                        </div>
                        <div style="height:6px;background:var(--fog);border-radius:3px;overflow:hidden;margin-bottom:4px;"><div style="width:{{ $pct }}%;height:100%;background:{{ $isLow ? 'var(--coral)' : 'var(--sky-dark)' }};border-radius:3px;"></div></div>
                    </div>
                @endforeach
            </div>

            <!-- Crew Button -->
            <a href="{{ route('caretaker.crew.index') }}" style="display:flex;align-items:center;justify-content:center;gap:6px;padding:8px;background:var(--navy);border-radius:8px;text-decoration:none;cursor:pointer;margin-top:8px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                <span style="font-family:var(--font-sub);font-size:0.65rem;color:var(--pin-white);">My Crew</span>
                <span style="font-family:var(--font-mono);font-size:0.5rem;color:var(--gold);">{{ $relationships->count() }} &#8594;</span>
            </a>

            <!-- Profile -->
            <div style="margin-top:8px;padding-top:8px;border-top:2px solid var(--fog);text-align:center;">
                <div class="ball-avatar ball-sm ball-coral" style="margin:0 auto;"><div class="ball-holes"><span></span><span></span><span></span></div><span class="ball-initials">{{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr($user->name, strrpos($user->name, ' ') + 1, 1)) }}</span></div>
                <div style="font-family:var(--font-sub);font-size:0.6rem;margin-top:4px;">{{ ucfirst($user->name) }}</div>
                <span class="badge-role caretaker" style="font-size:0.45rem;padding:2px 6px;">Caretaker</span>
                @if($personalities->count())
                    <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);margin-top:2px;">{{ $personalities->pluck('trait_name')->implode(' · ') }}</div>
                @endif
            </div>
        </div>

    </div>

    <x-toast />

    @include('sim.partials.responsive')
</x-app-layout>
