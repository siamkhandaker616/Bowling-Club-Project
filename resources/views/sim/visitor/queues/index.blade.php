<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Waiting Queue</h2>
            <span class="badge-role member">Visitor</span>
        </div>
    </x-slot>

    <style>
        .sim-nav{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:1rem;}
        .sim-nav a{padding:6px 14px;border-radius:50px;border:2px solid var(--navy);font-family:var(--font-sub);font-size:0.65rem;text-transform:uppercase;text-decoration:none;color:var(--navy);background:var(--pin-white);}
        .sim-nav a.active{background:var(--navy);color:var(--pin-white);}
    </style>

    <div style="zoom:1.25;padding:0 1rem;max-width:900px;margin:0 auto;">

        <div class="sim-nav">
            <a href="{{ route('visitor.dashboard') }}">Dashboard</a>
            <a href="{{ route('visitor.bookings.create') }}">Book a Lane</a>
            <a href="{{ route('visitor.bookings.index') }}">My Bookings</a>
            <a href="{{ route('visitor.queues.index') }}" class="active">Queue</a>
            <a href="{{ route('visitor.reviews.index') }}">Reviews</a>
            <a href="{{ route('visitor.complaints.index') }}">Complaints</a>
            <a href="/game">Play Bowling</a>
        </div>

        <div style="display:flex;flex-direction:column;gap:10px;">
            @forelse ($entries as $entry)
                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;display:flex;justify-content:space-between;align-items:center;">
                    <div style="display:flex;gap:10px;align-items:center;">
                        <span style="font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:var(--navy);color:var(--pin-white);">Pos {{ $entry->position }}</span>
                        <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">{{ $entry->date->format('M j, Y') }}</span>
                        <span style="font-family:var(--font-sub);font-size:0.75rem;">Lane {{ $entry->booking?->lane?->lane_number ?? '—' }} · {{ $entry->time_slot }}</span>
                        <span style="font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:{{ $entry->status === 'waiting' ? 'var(--gold-light)' : 'var(--sky)' }};color:var(--navy);border:1px solid var(--navy);">{{ $entry->status }}</span>
                    </div>
                </div>
            @empty
                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;text-align:center;">
                    <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">You're not in any waiting queue.</span>
                </div>
            @endforelse
        </div>

    </div>

    <x-toast />

    @include('sim.partials.responsive')
</x-app-layout>
