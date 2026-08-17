<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">My Shifts</h2>

        </div>
    </x-slot>

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div style="display:grid;grid-template-columns:180px 1fr 220px;gap:0;">
        <div style="background:var(--sky-light);border-right:3px solid var(--navy);padding:1rem;display:flex;flex-direction:column;">
            <div class="dash-section-label">Shift Context</div>
            <div style="display:flex;flex-direction:column;gap:6px;margin-top:8px;">
                @forelse ($shifts->whereIn('status', ['in_progress', 'scheduled']) as $shift)
                    <div style="padding:8px;border-radius:8px;background:var(--pin-white);border:2px solid var(--navy);">
                        <div style="font-family:var(--font-sub);font-size:0.7rem;color:var(--navy);font-weight:700;">Active Shift</div>
                        <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);margin-top:4px;">{{ $shift->date }}</div>
                        <span class="dash-stat-num" style="font-size:0.75rem;">{{ \App\Helpers\Label::timeSlot($shift->time_slot) }}</span>
                        @if ($shift->lane)
                            <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);margin-top:4px;">Lane {{ $shift->lane->lane_number }}</div>
                        @endif
                    </div>
                @empty
                    <div style="padding:8px;border-radius:8px;background:var(--pin-white);border:1px solid var(--fog);">
                        <div style="font-family:var(--font-sub);font-size:0.7rem;color:var(--slate);">No active shifts</div>
                    </div>
                @endforelse
                <div style="margin-top:auto;padding-top:8px;border-top:2px solid var(--fog);text-align:center;">
                    <div class="ball-avatar caretaker" style="width:48px;height:48px;border-radius:50%;background:var(--navy);color:var(--pin-white);display:inline-flex;align-items:center;justify-content:center;font-family:var(--font-header);font-size:1.1rem;font-weight:700;">CK</div>
                    <div style="font-family:var(--font-sub);font-size:0.65rem;color:var(--navy);margin-top:4px;font-weight:700;">Caretaker</div>
                </div>
            </div>
        </div>
        <div style="padding:1.25rem;overflow:hidden;">
            <div class="dash-section-label">Shift History</div>
            <div style="display:flex;flex-direction:column;gap:8px;margin-top:8px;">
                @forelse ($shifts as $shift)
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;display:flex;justify-content:space-between;align-items:center;">
                        <div style="display:flex;gap:10px;align-items:center;">
                            <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">{{ $shift->date }}</span>
                            <span class="badge-role" style="background:var(--sky);color:var(--sky-dark);border:1px solid var(--sky-dark);font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;">{{ \App\Helpers\Label::timeSlot($shift->time_slot) }}</span>
                            @if ($shift->lane)
                                <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">Lane {{ $shift->lane->lane_number }}</span>
                            @endif
                            <span style="font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:{{ $shift->status === 'completed' ? 'var(--sky)' : ($shift->status === 'cancelled' ? 'var(--coral-light)' : 'var(--gold-light)') }};color:var(--navy);border:1px solid var(--navy);">{{ \App\Helpers\Label::shiftStatus($shift->status) }}</span>
                            @if ($shift->status === 'completed')
                                <span class="pin standing" title="Completed" style="color:var(--sky-dark);font-size:0.75rem;">&#9679;</span>
                            @elseif ($shift->status === 'cancelled')
                                <span class="pin knocked" title="Cancelled" style="color:var(--coral-dark);font-size:0.75rem;">&#9746;</span>
                            @else
                                <span class="pin standing" title="In progress" style="color:var(--gold);font-size:0.75rem;">&#9679;</span>
                            @endif
                        </div>
                        <div style="display:flex;gap:8px;">
                            @if (in_array($shift->status, ['in_progress', 'scheduled']))
                                <form method="POST" action="{{ route('caretaker.shifts.complete', $shift) }}">
                                    @csrf
                                    <button type="submit" class="btn-lane primary" style="font-size:0.55rem;padding:4px 10px;">Complete</button>
                                </form>
                                <form method="POST" action="{{ route('caretaker.shifts.cancel', $shift) }}">
                                    @csrf
                                    <button type="submit" class="btn-lane secondary" style="font-size:0.55rem;padding:4px 10px;">Cancel</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;text-align:center;">
                        <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">No shifts assigned yet.</span>
                    </div>
                @endforelse
            </div>
        </div>
        <div style="background:var(--sky-light);border-left:3px solid var(--navy);padding:1rem;display:flex;flex-direction:column;">
            <div class="dash-section-label">This Week</div>
            <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);margin-top:6px;">{{ $weekLabel }}</div>
            <div style="display:flex;align-items:baseline;gap:6px;margin-top:8px;">
                <span class="dash-stat-num" style="font-size:1.4rem;">{{ $weekDone }}<span style="font-size:0.7rem;color:var(--slate);">/{{ $weekTotal }}</span></span>
                <span class="dash-stat-label">shifts done</span>
            </div>
            <div style="height:8px;background:var(--fog);border-radius:4px;overflow:hidden;margin-top:8px;">
                <div style="height:100%;width:{{ $weekTotal ? (int) round($weekDone / $weekTotal * 100) : 0 }}%;background:var(--sky-dark);border-radius:4px;"></div>
            </div>
            <div class="dash-section-label" style="margin-top:16px;">Status</div>
            <div class="dash-stat" style="margin-top:8px;">
                <span class="dash-stat-num">{{ $shifts->where('status','completed')->count() }}</span>
                <span class="dash-stat-label">Completed</span>
            </div>
            <div class="dash-stat" style="margin-top:6px;">
                <span class="dash-stat-num" style="color:var(--gold);">{{ $shifts->whereIn('status', ['scheduled', 'in_progress'])->count() }}</span>
                <span class="dash-stat-label">Pending</span>
            </div>
        </div>
    </div>
    </div>

    <x-toast />

    @include('sim.partials.responsive')
</x-app-layout>
