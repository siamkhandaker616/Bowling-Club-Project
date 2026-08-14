<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">My Complaints</h2>
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
            <a href="{{ route('visitor.queues.index') }}">Queue</a>
            <a href="{{ route('visitor.reviews.index') }}">Reviews</a>
            <a href="{{ route('visitor.complaints.index') }}" class="active">Complaints</a>
            <a href="/game">Play Bowling</a>
        </div>

        <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;margin-bottom:1.25rem;">
            <div class="dash-section-label">Submit a Complaint</div>
            <form method="POST" action="{{ route('visitor.complaints.store') }}" style="display:grid;grid-template-columns:1fr 2fr auto;gap:8px;margin-top:8px;">
                @csrf
                <select name="type" required style="font-family:var(--font-body);font-size:0.7rem;padding:6px 8px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                    <option value="service">Service</option>
                    <option value="cleanliness">Cleanliness</option>
                    <option value="behavior">Behavior</option>
                    <option value="facility">Facility</option>
                    <option value="other">Other</option>
                </select>
                <input name="description" type="text" placeholder="What went wrong?" required style="font-family:var(--font-body);font-size:0.7rem;padding:6px 8px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                <button type="submit" class="btn-lane primary" style="font-size:0.55rem;padding:5px 12px;">Submit</button>
            </form>
        </div>

        <div class="dash-section-label">My Complaint History</div>
        <div style="display:flex;flex-direction:column;gap:10px;">
            @forelse ($complaints as $complaint)
                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;">
                    <div style="display:flex;gap:8px;align-items:center;">
                        <span style="font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:{{ match($complaint->status) { 'open' => 'var(--coral-light)', 'investigating' => 'var(--gold-light)', 'resolved' => 'var(--sky)', default => 'var(--mist)' } }};color:var(--navy);border:1px solid var(--navy);">{{ $complaint->status }}</span>
                        <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">{{ $complaint->type }} · {{ $complaint->created_at->format('M j, H:i') }}</span>
                    </div>
                    <div style="font-family:var(--font-body);font-size:0.72rem;color:var(--navy);margin-top:6px;background:var(--pin-white);border-radius:8px;padding:8px 10px;border:1px solid var(--fog);">{{ $complaint->description }}</div>
                    @if ($complaint->resolution)
                        <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--sky-dark);margin-top:6px;">Resolution: {{ $complaint->resolution }}@if ($complaint->compensation_type) ({{ $complaint->compensation_type }})@endif</div>
                    @endif
                </div>
            @empty
                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;text-align:center;">
                    <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">You have no complaints. Great visit!</span>
                </div>
            @endforelse
        </div>

    </div>

    <x-toast />

    @include('sim.partials.responsive')
</x-app-layout>
