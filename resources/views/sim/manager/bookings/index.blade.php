<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Bookings & Queue</h2>
            <div style="display:flex;align-items:center;gap:1rem;">
                <span class="badge-role manager">Manager</span>
            </div>
        </div>
    </x-slot>

    <style>
        .bk-table-header{background:var(--navy);border-radius:8px;display:grid;grid-template-columns:80px 1fr 110px 90px 110px 80px;gap:8px;padding:8px 12px;align-items:center;}
        .bk-row{display:grid;grid-template-columns:80px 1fr 110px 90px 110px 80px;gap:8px;padding:8px 12px;align-items:center;border-bottom:1px solid var(--fog);}
        .bk-badge{font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;}
    </style>

    <div style="zoom:1.25;display:grid;grid-template-columns:200px 1fr;gap:0;min-height:calc(100vh - 200px);">

        <div class="dash-sidebar">
            <div class="dash-section-label" style="margin-bottom:4px;">Modules</div>
            <a href="{{ route('manager.dashboard') }}" class="dash-sidebar-link">Overview</a>
            <a href="{{ route('manager.staff.index') }}" class="dash-sidebar-link">Staff</a>
            <a href="{{ route('manager.inventory.index') }}" class="dash-sidebar-link">Inventory</a>
            <a href="{{ route('manager.bookings.index') }}" class="dash-sidebar-link active">Bookings</a>
            <a href="{{ route('manager.bans.index') }}" class="dash-sidebar-link">Bans</a>
            <a href="{{ route('manager.complaints.index') }}" class="dash-sidebar-link">Complaints</a>
            <a href="{{ route('manager.confrontations.index') }}" class="dash-sidebar-link">Confrontations</a>
            <a href="{{ route('manager.reviews.index') }}" class="dash-sidebar-link">Reviews</a>
            <a href="{{ route('manager.touring.index') }}" class="dash-sidebar-link">Touring</a>
            <div style="margin-top:auto;padding-top:0.75rem;border-top:2px solid var(--fog);text-align:center;">
                @php $u = auth()->user(); @endphp
                <div class="ball-avatar ball-sm ball-navy" style="margin:0 auto;"><div class="ball-holes"><span></span><span></span><span></span></div><span class="ball-initials">{{ strtoupper(substr($u->name,0,1)) }}{{ strtoupper(substr(str_replace(' ','',$u->name),-1)) }}</span></div>
                <div style="font-family:var(--font-sub);font-size:0.65rem;margin-top:4px;">{{ ucfirst($u->name) }}</div>
                <span class="badge-role manager" style="font-size:0.5rem;padding:2px 8px;">Manager</span>
            </div>
        </div>

        <div style="padding:1.25rem;overflow:hidden;">

            <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;margin-bottom:1rem;">
                <div class="dash-section-label">Waiting Queue</div>
                @forelse ($queues->where('status', 'waiting') as $entry)
                    <div style="display:grid;grid-template-columns:1fr 1fr 90px;gap:8px;padding:8px 0;align-items:center;border-bottom:1px solid var(--fog);">
                        <span style="font-family:var(--font-sub);font-size:0.7rem;">{{ $entry->visitor?->name }}</span>
                        <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">{{ $entry->date }} · {{ $entry->time_slot }}</span>
                        <span class="bk-badge" style="background:var(--gold-light);color:var(--gold-dust);border:1px solid var(--gold);">Pos {{ $entry->position }}</span>
                    </div>
                @empty
                    <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">Queue is clear.</div>
                @endforelse
            </div>

            <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;">
                <div class="dash-section-label">All Bookings</div>
                <div class="bk-table-header">
                    <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--fog);">DATE</span>
                    <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--fog);">VISITOR</span>
                    <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--fog);">LANE</span>
                    <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--fog);">SLOT</span>
                    <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--fog);">STATUS</span>
                    <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--fog);"></span>
                </div>
                @forelse ($bookings as $booking)
                    @php
                        $badge = match ($booking->status) {
                            'confirmed' => ['background' => 'var(--sky)', 'color' => 'var(--sky-dark)', 'border' => '1px solid var(--sky-dark)'],
                            'completed' => ['background' => 'var(--mist)', 'color' => 'var(--slate)', 'border' => '1px solid var(--fog)'],
                            'pending' => ['background' => 'var(--gold-light)', 'color' => 'var(--gold-dust)', 'border' => '1px solid var(--gold)'],
                            default => ['background' => 'var(--coral-light)', 'color' => 'var(--coral-dark)', 'border' => '1px solid var(--coral)'],
                        };
                    @endphp
                    <div class="bk-row">
                        <span style="font-family:var(--font-mono);font-size:0.6rem;">{{ $booking->date->format('M j') }}</span>
                        <span style="font-family:var(--font-sub);font-size:0.7rem;">{{ $booking->visitor?->name }}</span>
                        <span style="font-family:var(--font-mono);font-size:0.6rem;">L{{ $booking->lane?->lane_number ?? '—' }}</span>
                        <span style="font-family:var(--font-mono);font-size:0.6rem;">{{ $booking->time_slot }}</span>
                        <span class="bk-badge" style="{{ collect($badge)->map(fn ($v, $k) => "$k:$v")->implode(';') }};">{{ $booking->status }}</span>
                        <span>
                            @if (in_array($booking->status, ['pending', 'confirmed']))
                                <form method="POST" action="{{ route('manager.bookings.cancel', $booking) }}">
                                    @csrf
                                    <button type="submit" class="btn-lane danger" style="font-size:0.5rem;padding:3px 10px;">Cancel</button>
                                </form>
                            @endif
                        </span>
                    </div>
                @empty
                    <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);padding:8px;">No bookings yet.</div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
