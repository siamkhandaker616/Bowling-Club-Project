<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Reviews</h2>
            <span class="badge-role member">Visitor</span>
        </div>
    </x-slot>


    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div style="padding:0 1rem;max-width:900px;margin:0 auto;">

        

        @if ($completedBookings->count())
            <div class="dash-section-label">Review a Completed Visit</div>
            <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:1.25rem;">
                @foreach ($completedBookings as $booking)
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;">
                        <div style="font-family:var(--font-sub);font-size:0.72rem;margin-bottom:8px;">Lane {{ $booking->lane?->lane_number ?? '—' }} · {{ $booking->date->format('M j, Y') }} · {{ $booking->time_slot }}</div>
                        <form method="POST" action="{{ route('visitor.reviews.store', $booking) }}" style="display:grid;grid-template-columns:auto 1fr auto;gap:8px;">
                            @csrf
                            <select name="rating" required class="fold-select" style="font-family:var(--font-body);font-size:0.7rem;padding:6px 8px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                                <option value="5">5 ★</option>
                                <option value="4">4 ★</option>
                                <option value="3">3 ★</option>
                                <option value="2">2 ★</option>
                                <option value="1">1 ★</option>
                            </select>
                            <input name="body" type="text" placeholder="Your comments (optional)" style="font-family:var(--font-body);font-size:0.7rem;padding:6px 8px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                            <button type="submit" class="btn-lane primary" style="font-size:0.55rem;padding:6px 12px;">Submit</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="dash-section-label">My Reviews</div>
        <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;margin-bottom:1.25rem;display:flex;flex-direction:column;gap:8px;">
            @forelse ($mine as $review)
                <div style="border-bottom:1px solid var(--fog);padding-bottom:8px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:var(--gold-dust);font-family:var(--font-mono);font-size:0.85rem;letter-spacing:2px;">{{ str_repeat('★', $review->rating) }}<span style="color:var(--fog);">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                        <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">{{ $review->created_at->format('M j, H:i') }}</span>
                    </div>
                    <div style="font-family:var(--font-body);font-size:0.7rem;color:var(--navy);margin-top:4px;">{{ $review->body }}</div>
                </div>
            @empty
                <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">You haven't written any reviews yet.</span>
            @endforelse
        </div>

        <div class="dash-section-label">Recent Venue Reviews</div>
        <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;display:flex;flex-direction:column;gap:10px;">
            @forelse ($allReviews as $review)
                <div style="border-bottom:1px solid var(--fog);padding-bottom:8px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-family:var(--font-sub);font-size:0.75rem;">{{ $review->visitor->name }}</span>
                        <span style="color:var(--gold-dust);font-family:var(--font-mono);font-size:0.85rem;letter-spacing:2px;">{{ str_repeat('★', $review->rating) }}<span style="color:var(--fog);">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                    </div>
                    <div style="font-family:var(--font-body);font-size:0.7rem;color:var(--navy);margin-top:4px;">{{ $review->body }}</div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:6px;">
                        <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">
                            @if ($review->booking?->lane)
                                Lane {{ $review->booking->lane->lane_number }} ·
                            @endif
                            {{ $review->created_at->format('M j, H:i') }}
                        </span>
                        <div style="display:flex;gap:6px;">
                            <form method="POST" action="{{ route('visitor.reviews.vote', $review) }}">
                                @csrf
                                <input type="hidden" name="vote" value="helpful">
                                <button type="submit" class="btn-lane secondary" style="font-size:0.5rem;padding:3px 8px;">Helpful ({{ $review->helpful_count ?? 0 }})</button>
                            </form>
                            <form method="POST" action="{{ route('visitor.reviews.vote', $review) }}">
                                @csrf
                                <input type="hidden" name="vote" value="not_helpful">
                                <button type="submit" class="btn-lane secondary" style="font-size:0.5rem;padding:3px 8px;">Not helpful ({{ $review->not_helpful_count ?? 0 }})</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">No venue reviews yet.</span>
            @endforelse
        </div>

    </div>
    </div>

    <x-toast />

    @include('sim.partials.fold-controls')
    @include('sim.partials.responsive')
</x-app-layout>
