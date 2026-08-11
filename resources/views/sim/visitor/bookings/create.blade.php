<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Book a Lane</h2>
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
            <a href="{{ route('visitor.bookings.create') }}" class="active">Book a Lane</a>
            <a href="{{ route('visitor.bookings.index') }}">My Bookings</a>
            <a href="{{ route('visitor.queues.index') }}">Queue</a>
            <a href="{{ route('visitor.reviews.index') }}">Reviews</a>
            <a href="{{ route('visitor.complaints.index') }}">Complaints</a>
            <a href="/game">Play Bowling</a>
        </div>

        @if (! $visitor)
            <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;text-align:center;">
                <span style="font-family:var(--font-mono);font-size:0.7rem;color:var(--slate);">No visitor profile is linked to your account yet. The simulation seeds visitors automatically — check back soon.</span>
            </div>
        @elseif ($visitor->is_banned)
            <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;text-align:center;">
                <span style="font-family:var(--font-mono);font-size:0.7rem;color:var(--coral-dark);">You are currently banned from booking lanes.</span>
            </div>
        @else
            <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;">
                <div class="dash-section-label">New Reservation</div>
                <form method="POST" action="{{ route('visitor.bookings.store') }}" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;margin-top:10px;">
                    @csrf
                    <div>
                        <label style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">DATE</label>
                        <input type="date" name="date" value="{{ $date->toDateString() }}" required style="width:100%;font-family:var(--font-body);font-size:0.75rem;padding:8px 10px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                    </div>
                    <div>
                        <label style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">LANE</label>
                        <select name="lane_id" required style="width:100%;font-family:var(--font-body);font-size:0.75rem;padding:8px 10px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                            @foreach ($lanes as $lane)
                                <option value="{{ $lane->id }}">Lane {{ $lane->lane_number }} ({{ $lane->status }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">TIME SLOT</label>
                        <select name="time_slot" required style="width:100%;font-family:var(--font-body);font-size:0.75rem;padding:8px 10px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                            @foreach ($slots as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="align-self:end;">
                        <button type="submit" class="btn-lane primary" style="padding:8px 18px;font-size:0.6rem;">Book</button>
                    </div>
                </form>
                <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);margin-top:10px;">If the lane is already taken for that slot, you'll join the waiting queue automatically.</div>
            </div>
        @endif

    </div>
</x-app-layout>
