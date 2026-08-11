<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Today's Schedule</h2>
            <span class="badge-role steward">Steward</span>
        </div>
    </x-slot>

    <div style="zoom:1.25;display:grid;grid-template-columns:220px 1fr 180px;gap:0;min-height:calc(100vh - 200px);">

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
                                    <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">{{ ucfirst($slot) }}</div>
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
                <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">{{ $staffOnDuty }} staff · {{ $shifts->count() }} shifts</span>
            </div>
        </div>

        <div style="padding:1.25rem;overflow:hidden;">

            <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;padding:0.75rem 1rem;display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;">
                <span style="font-family:var(--font-header);font-size:0.85rem;color:var(--navy);text-transform:uppercase;">{{ $date->format('D, M j Y') }}</span>
                <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">{{ $staffOnDuty }} staff on duty · {{ $shifts->count() }} shifts</span>
            </div>

            <div style="margin-bottom:1.25rem;">
                <div class="dash-section-label" style="margin-bottom:8px;">Staff Shifts</div>
                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;display:flex;flex-direction:column;gap:0;">
                    @forelse ($shifts as $shift)
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;{{ !$loop->last ? 'border-bottom:1px solid var(--fog);' : '' }}">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <span style="font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:var(--sky);color:var(--sky-dark);border:1px solid var(--sky-dark);">{{ $shift->time_slot }}</span>
                                <span style="font-family:var(--font-sub);font-size:0.75rem;">{{ $shift->staff->user->name }}</span>
                                <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">{{ $shift->staff->role }}</span>
                            </div>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <span style="font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:{{ $shift->is_complete ? 'var(--sky)' : 'var(--gold-light)' }};color:var(--navy);border:1px solid var(--navy);">{{ $shift->is_complete ? 'complete' : 'on shift' }}</span>
                                @if (! $shift->is_complete)
                                    <form method="POST" action="{{ route('steward.schedule.complete', $shift) }}">
                                        @csrf
                                        <button type="submit" class="btn-lane primary" style="font-size:0.55rem;padding:4px 10px;">Mark Complete</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);padding:12px;">No shifts today.</span>
                    @endforelse
                </div>
            </div>

            <div>
                <div class="dash-section-label" style="margin-bottom:8px;">Today's Bookings</div>
                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;display:flex;flex-direction:column;gap:0;">
                    @forelse ($bookings as $booking)
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;{{ !$loop->last ? 'border-bottom:1px solid var(--fog);' : '' }}">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <span style="font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:var(--navy);color:var(--pin-white);">{{ $booking->time_slot }}</span>
                                <span style="font-family:var(--font-sub);font-size:0.75rem;">{{ $booking->visitor->name }}</span>
                            </div>
                            <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">Lane {{ $booking->lane?->lane_number ?? '—' }} · {{ $booking->status }}</span>
                        </div>
                    @empty
                        <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);padding:12px;">No bookings today.</span>
                    @endforelse
                </div>
            </div>

        </div>

        <div style="background:var(--sky-light);border-left:3px solid var(--navy);padding:0.75rem;display:flex;flex-direction:column;gap:6px;align-items:center;">
            <div class="dash-section-label" style="margin-bottom:2px;width:100%;">Quick Actions</div>
            <a href="{{ route('steward.schedule.index') }}" class="shoe-tag" style="width:70%;"><div class="st-shape" style="padding:0.5rem 0.5rem 1rem;"><div class="st-icon" style="font-size:1.2rem;margin:0.4rem 0 0.2rem;">&#128197;</div><h4 style="font-size:0.6rem;">Schedule</h4></div></a>
            <a href="{{ route('steward.bans.index') }}" class="shoe-tag white" style="width:70%;"><div class="st-shape" style="padding:0.5rem 0.5rem 1rem;"><div class="st-icon" style="font-size:1.2rem;margin:0.4rem 0 0.2rem;">&#128683;</div><h4 style="font-size:0.6rem;">Ban Request</h4></div></a>
            <a href="{{ route('steward.complaints.index') }}" class="shoe-tag coral" style="width:70%;"><div class="st-shape" style="padding:0.5rem 0.5rem 1rem;"><div class="st-icon" style="font-size:1.2rem;margin:0.4rem 0 0.2rem;">&#9878;</div><h4 style="font-size:0.6rem;">Complaints</h4></div></a>
            <a href="{{ route('steward.visitors.index') }}" class="shoe-tag" style="width:70%;"><div class="st-shape" style="padding:0.5rem 0.5rem 1rem;"><div class="st-icon" style="font-size:1.2rem;margin:0.4rem 0 0.2rem;">&#128100;</div><h4 style="font-size:0.6rem;">Visitors</h4></div></a>
            <div style="margin-top:auto;padding-top:8px;border-top:2px solid var(--fog);text-align:center;">
                <div class="ball-avatar ball-sm ball-sky" style="margin:0 auto;"><div class="ball-holes"><span></span><span></span><span></span></div><span class="ball-initials">ST</span></div>
                <div style="font-family:var(--font-sub);font-size:0.6rem;margin-top:4px;">{{ ucfirst($user->name ?? Auth::user()->name) }}</div>
                <span class="badge-role steward" style="font-size:0.45rem;padding:2px 6px;">Steward</span>
            </div>
        </div>

    </div>
</x-app-layout>
