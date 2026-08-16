<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Steward Dashboard</h2>
            <div style="display:flex;align-items:center;gap:1rem;">
                <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--pin-white);">{{ $stats['checked_in'] }} checked in</span>
                <span class="badge-role steward">Steward</span>
            </div>
        </div>
    </x-slot>

    @if ($staff && ! $staff->is_active)
        <div style="margin:0.75rem 0;padding:0.75rem 1rem;border:2px solid var(--coral-dark);background:var(--coral-light);border-radius:10px;display:flex;align-items:center;justify-content:space-between;gap:1rem;">
            <div>
                <div style="font-family:var(--font-sub);font-size:0.75rem;color:var(--navy);font-weight:700;">You are no longer on the roster.</div>
                <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">Your employment ended with the club. You can reapply with a new identity.</div>
            </div>
            <a href="{{ route('reapply.index') }}" class="btn-lane primary" style="font-size:0.6rem;padding:6px 12px;text-decoration:none;white-space:nowrap;">Reapply Now</a>
        </div>
    @endif

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.steward-sidebar')

        <!-- LEFT: Today's Schedule -->
        <div style="display:grid;grid-template-columns:220px 1fr;gap:0;">
        <div style="background:var(--sky-light);border-right:3px solid var(--navy);padding:1rem;display:flex;flex-direction:column;">
            <div class="dash-section-label" style="margin-bottom:8px;">Today's Schedule</div>
            <div style="display:flex;flex-direction:column;gap:2px;flex:1;">
                @php
                    $timeSlots = ['morning' => '10:00', 'afternoon' => '14:00', 'evening' => '18:00'];
                    $bookingsBySlot = $bookings->groupBy('time_slot');
                    $shiftsBySlot = $shifts->groupBy('time_slot');
                @endphp
                @foreach(['morning', 'afternoon', 'evening'] as $slot)
                    @php
                        $slotBookings = $bookingsBySlot[$slot] ?? collect();
                        $slotShifts = $shiftsBySlot[$slot] ?? collect();
                        $hasContent = $slotBookings->count() > 0 || $slotShifts->count() > 0;
                    @endphp
                    <div style="display:flex;align-items:stretch;gap:8px;">
                        <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);width:38px;text-align:right;padding-top:6px;">{{ $timeSlots[$slot] }}</div>
                        @if($hasContent)
                            @if($slotBookings->count() > 0)
                                @php $b = $slotBookings->first(); @endphp
                                <div style="flex:1;padding:6px 8px;background:var(--sky);border-radius:6px;border-left:3px solid var(--sky-dark);">
                                    <div style="font-family:var(--font-sub);font-size:0.65rem;">Lane {{ $b->lane?->lane_number ?? '?' }} · {{ $b->visitor->name ?? 'Guest' }}</div>
                                    <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">{{ ucfirst($slot) }} · {{ $b->status }}</div>
                                </div>
                            @else
                                @php $s = $slotShifts->first(); @endphp
                                <div style="flex:1;padding:6px 8px;background:var(--gold-light);border-radius:6px;border-left:3px solid var(--gold);">
                                    <div style="font-family:var(--font-sub);font-size:0.65rem;">{{ $s->staff->user->name ?? 'Staff' }} on duty</div>
                                    <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">{{ ucfirst($slot) }} · {{ $s->lane?->lane_number ? 'Lane '.$s->lane->lane_number : '' }}</div>
                                </div>
                            @endif
                        @else
                            <div style="flex:1;padding:6px 8px;border:2px dashed var(--fog);border-radius:6px;">
                                <div style="font-family:var(--font-sub);font-size:0.65rem;color:var(--fog);">Available</div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            <div style="margin-top:8px;padding-top:8px;border-top:2px solid var(--fog);text-align:center;">
                <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">{{ $stats['today_bookings'] }} bookings · {{ $stats['staff_on_duty'] }} staff on duty</span>
            </div>
        </div>

        <!-- CENTER: Visitor Directory -->
        <div style="padding:1.25rem;overflow:hidden;">

            <!-- Visitor Directory -->
            <div style="margin-bottom:1.25rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <div class="dash-section-label" style="margin:0;">Visitor Directory</div>
                    <div style="display:flex;gap:6px;">
                        <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">{{ $stats['total_visitors'] }} total · {{ $stats['premium'] }} premium · {{ $stats['banned'] }} banned</span>
                    </div>
                </div>
                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;overflow:hidden;">
                    <div style="display:grid;grid-template-columns:40px 1fr 90px 80px 70px;padding:6px 12px;background:var(--navy);gap:8px;">
                        <span></span>
                        <span style="font-family:var(--font-mono);font-size:0.5rem;color:var(--fog);">VISITOR</span>
                        <span style="font-family:var(--font-mono);font-size:0.5rem;color:var(--fog);">TIER</span>
                        <span style="font-family:var(--font-mono);font-size:0.5rem;color:var(--fog);">BOOKINGS</span>
                        <span style="font-family:var(--font-mono);font-size:0.5rem;color:var(--fog);">STATUS</span>
                    </div>
                    @foreach($visitors->take(8) as $v)
                        @php
                            $initials = strtoupper(substr($v->name, 0, 1)) . (strlen($v->name) > 1 ? strtoupper(substr(str_replace(' ', '', $v->name), -1)) : '');
                            $ballColor = $v->tier === 'premium' ? 'ball-gold' : ($v->is_banned ? 'ball-coral' : 'ball-sky');
                        @endphp
                        <div style="display:grid;grid-template-columns:40px 1fr 90px 80px 70px;padding:8px 12px;{{ !$loop->last ? 'border-bottom:1px solid var(--fog);' : '' }}gap:8px;align-items:center;">
                            <div class="ball-avatar ball-sm {{ $ballColor }}"><div class="ball-holes"><span></span><span></span><span></span></div><span class="ball-initials">{{ $initials }}</span></div>
                            <div><div style="font-family:var(--font-sub);font-size:0.7rem;">{{ $v->name }}</div><div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">{{ $v->email ?? '' }}</div></div>
                            <span class="badge-role {{ $v->tier === 'premium' ? 'member' : 'steward' }}" style="font-size:0.5rem;width:fit-content;">{{ ucfirst($v->tier) }}</span>
                            <span style="font-family:var(--font-mono);font-size:0.6rem;">{{ $v->bookings_count ?? 0 }}</span>
                            <span style="font-family:var(--font-mono);font-size:0.55rem;color:{{ $v->is_banned ? 'var(--coral-dark)' : 'var(--sky-dark)' }};">{{ $v->is_banned ? '&#10008; Banned' : '&#9679; Active' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- RIGHT rail removed: those links already live in the dock above -->

        </div>

    </div>

    <x-toast />
    @include('sim.partials.responsive')
</x-app-layout>
