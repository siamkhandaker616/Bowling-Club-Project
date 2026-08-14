<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Visitor & Staff Reviews</h2>
            <div style="display:flex;align-items:center;gap:1rem;">
                <span class="badge-role manager">Manager</span>
            </div>
        </div>
    </x-slot>

    <style>
        .rev-stars{color:var(--gold-dust);font-family:var(--font-mono);font-size:0.85rem;letter-spacing:2px;}
        .rev-card{background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;display:flex;flex-direction:column;gap:10px;}
        .rev-item{border-bottom:1px solid var(--fog);padding-bottom:8px;}
    </style>

    <div style="zoom:1.25;display:grid;grid-template-columns:200px 1fr;gap:0;min-height:calc(100vh - 200px);">

        <div class="dash-sidebar">
            <div class="dash-section-label" style="margin-bottom:4px;">Modules</div>
            <a href="{{ route('manager.dashboard') }}" class="dash-sidebar-link">Overview</a>
            <a href="{{ route('manager.staff.index') }}" class="dash-sidebar-link">Staff</a>
            <a href="{{ route('manager.inventory.index') }}" class="dash-sidebar-link">Inventory</a>
            <a href="{{ route('manager.bookings.index') }}" class="dash-sidebar-link">Bookings</a>
            <a href="{{ route('manager.bans.index') }}" class="dash-sidebar-link">Bans</a>
            <a href="{{ route('manager.complaints.index') }}" class="dash-sidebar-link">Complaints</a>
            <a href="{{ route('manager.confrontations.index') }}" class="dash-sidebar-link">Confrontations</a>
            <a href="{{ route('manager.reviews.index') }}" class="dash-sidebar-link active">Reviews</a>
            <a href="{{ route('manager.touring.index') }}" class="dash-sidebar-link">Touring</a>
            <div style="margin-top:auto;padding-top:0.75rem;border-top:2px solid var(--fog);text-align:center;">
                @php $u = auth()->user(); @endphp
                <div class="ball-avatar ball-sm ball-navy" style="margin:0 auto;"><div class="ball-holes"><span></span><span></span><span></span></div><span class="ball-initials">{{ strtoupper(substr($u->name,0,1)) }}{{ strtoupper(substr(str_replace(' ','',$u->name),-1)) }}</span></div>
                <div style="font-family:var(--font-sub);font-size:0.65rem;margin-top:4px;">{{ ucfirst($u->name) }}</div>
                <span class="badge-role manager" style="font-size:0.5rem;padding:2px 8px;">Manager</span>
            </div>
        </div>

        <div style="padding:1.25rem;overflow:hidden;">

            <div class="dash-section-label">Venue Reviews</div>
            <div class="rev-card" style="margin-bottom:1.25rem;">
                @forelse ($visitorReviews as $review)
                    <div class="rev-item">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-family:var(--font-sub);font-size:0.75rem;">{{ $review->visitor->name }}</span>
                            <span class="rev-stars">{{ str_repeat('★', $review->rating) }}<span style="color:var(--fog);">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                        </div>
                        <div style="font-family:var(--font-body);font-size:0.7rem;color:var(--navy);margin-top:4px;">{{ $review->body }}</div>
                        <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);margin-top:4px;">
                            Lane {{ $review->booking?->lane?->lane_number ?? '—' }} · {{ $review->created_at->format('M j, H:i') }}
                            @if ($review->incident_type)<span style="color:var(--coral-dark);"> · incident: {{ $review->incident_type }}</span>@endif
                            @if ($review->satisfaction)<span> · satisfaction {{ number_format($review->satisfaction, 1) }}</span>@endif
                        </div>
                    </div>
                @empty
                    <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">No venue reviews yet.</span>
                @endforelse
            </div>

            <div class="dash-section-label">Staff Reviews</div>
            <div class="rev-card">
                @forelse ($staffReviews as $review)
                    <div class="rev-item">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-family:var(--font-sub);font-size:0.75rem;">{{ $review->staff->user->name }}</span>
                            <span class="rev-stars">{{ str_repeat('★', $review->rating) }}<span style="color:var(--fog);">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                        </div>
                        <div style="font-family:var(--font-body);font-size:0.7rem;color:var(--navy);margin-top:4px;">{{ $review->body }}</div>
                        <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);margin-top:4px;">
                            by {{ $review->visitor->name }} · {{ $review->created_at->format('M j, H:i') }}
                            @if ($review->tip_amount)<span style="color:var(--gold-dust);"> · tip ${{ number_format($review->tip_amount, 2) }}</span>@endif
                        </div>
                    </div>
                @empty
                    <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">No staff reviews yet.</span>
                @endforelse
            </div>

        </div>
    </div>

    <x-toast />

    @include('sim.partials.responsive')
</x-app-layout>
