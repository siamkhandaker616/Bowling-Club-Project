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

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

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
                            @if (($review->helpful_count ?? 0) > 0)<span style="color:var(--ok);"> · {{ $review->helpful_count }} helpful</span>@endif
                            @if (($review->not_helpful_count ?? 0) > 0)<span style="color:var(--coral-dark);"> · {{ $review->not_helpful_count }} not helpful</span>@endif
                        </div>
                    </div>
                @empty
                    <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">No venue reviews yet.</span>
                @endforelse
            </div>

            <div class="dash-section-label">Staff Reviews of Visitors</div>
            <div class="rev-card">
                @forelse ($staffReviews as $review)
                    <div class="rev-item">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-family:var(--font-sub);font-size:0.75rem;">{{ $review->visitor->name }}</span>
                            <span class="rev-stars">{{ str_repeat('★', $review->rating) }}<span style="color:var(--fog);">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                        </div>
                        <div style="font-family:var(--font-body);font-size:0.7rem;color:var(--navy);margin-top:4px;">{{ $review->body }}</div>
                        <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);margin-top:4px;">
                            by {{ $review->staff->user->name }} · Lane {{ $review->booking?->lane?->lane_number ?? '—' }} · {{ $review->created_at->format('M j, H:i') }}
                            @if ($review->was_polite)<span style="color:var(--ok);"> · polite</span>@endif
                            @if ($review->caused_issues)<span style="color:var(--coral-dark);"> · caused issues</span>@endif
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
