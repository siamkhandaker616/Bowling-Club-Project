<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Visitors</h2>

        </div>
    </x-slot>

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div style="display:grid;grid-template-columns:220px 1fr;gap:0;">

        <div style="background:var(--sky-light);border-right:3px solid var(--navy);padding:1rem;display:flex;flex-direction:column;">
            <div class="dash-section-label" style="margin-bottom:8px;">Upcoming Check-ins</div>
            <div style="display:flex;flex-direction:column;gap:4px;flex:1;">
                @forelse ($checkIns as $booking)
                    <div style="padding:6px 8px;background:var(--sky);border-radius:6px;border-left:3px solid var(--sky-dark);">
                        <div style="font-family:var(--font-sub);font-size:0.65rem;">{{ $booking->visitor->name }}</div>
                        <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">{{ \App\Helpers\Label::timeSlotFull($booking->time_slot) }} · Lane {{ $booking->lane?->lane_number ?? '—' }}</div>
                    </div>
                @empty
                    <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">No upcoming check-ins.</span>
                @endforelse
            </div>
            <div style="margin-top:8px;padding-top:8px;border-top:2px solid var(--fog);text-align:center;">
                <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">{{ $checkIns->count() }} upcoming</span>
            </div>
        </div>

        <div style="padding:1.25rem;overflow:hidden;">
            <div class="dash-section-label" style="margin-bottom:8px;">All Visitors</div>
            <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;overflow:hidden;">
                @forelse ($visitors as $visitor)
                    @php
                        $completed = $visitor->bookings->where('status', 'completed');
                        $hasReviewable = $completed->isNotEmpty();
                    @endphp
                    <div style="{{ !$loop->last ? 'border-bottom:1px solid var(--fog);' : '' }}">
                    <div style="display:flex;justify-content:space-between;padding:10px 12px;align-items:center;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            @php
                                $initials = strtoupper(substr($visitor->name, 0, 1)) . (strlen($visitor->name) > 1 ? strtoupper(substr(str_replace(' ', '', $visitor->name), -1)) : '');
                                $ballColor = $visitor->is_banned ? 'ball-coral' : ($visitor->tier === 'premium' ? 'ball-gold' : 'ball-sky');
                            @endphp
                            <div class="ball-avatar ball-sm {{ $ballColor }}"><div class="ball-holes"><span></span><span></span><span></span></div><span class="ball-initials">{{ $initials }}</span></div>
                            <div>
                                <div style="font-family:var(--font-sub);font-size:0.72rem;">{{ $visitor->name }}</div>
                                <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">{{ $visitor->bookings->count() }} visits · Reputation {{ $visitor->reputation_score }}</div>
                            </div>
                        </div>
                        <div style="display:flex;gap:6px;align-items:center;">
                            @if ($visitor->is_banned)
                                <span style="font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:var(--coral-light);color:var(--coral-dark);border:1px solid var(--coral);">BANNED</span>
                            @else
                                <span style="font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:var(--sky);color:var(--sky-dark);border:1px solid var(--sky-dark);">regular</span>
                            @endif
                            @if ($visitor->tier === 'premium')
                                <span style="font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:var(--gold-light);color:var(--gold-dust);border:1px solid var(--gold);">Premium</span>
                            @endif
                        </div>
                    </div>
                    @if ($hasReviewable)
                        <details style="padding:0 12px 10px;">
                            <summary style="cursor:pointer;font-family:var(--font-mono);font-size:0.55rem;color:var(--gold-dust);text-transform:uppercase;letter-spacing:1px;">Rate this visitor</summary>
                            <form method="POST" action="{{ route('steward.reviews.store', $visitor) }}" class="gutter-form" style="display:flex;flex-direction:column;gap:6px;margin-top:8px;padding:10px;background:var(--pin-white);border:1px solid var(--fog);border-radius:8px;">
                                @csrf
                                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                                    <label style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">Rating
                                        <select name="rating" class="fold-select" style="margin-left:4px;">
                                            @foreach ([1,2,3,4,5] as $r)
                                                <option value="{{ $r }}">{{ $r }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">Booking
                                        <select name="booking_id" class="fold-select" style="margin-left:4px;">
                                            @foreach ($completed as $b)
                                                <option value="{{ $b->id }}">{{ $b->date }} · {{ \App\Helpers\Label::timeSlot($b->time_slot) }} · L{{ $b->lane?->lane_number ?? '—' }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="pin-check" style="font-family:var(--font-mono);font-size:0.55rem;color:var(--navy);"><input type="checkbox" name="was_polite" value="1"><span class="pin-box"></span> Was polite</label>
                                    <label class="pin-check" style="font-family:var(--font-mono);font-size:0.55rem;color:var(--navy);"><input type="checkbox" name="caused_issues" value="1"><span class="pin-box"></span> Caused issues</label>
                                </div>
                                <textarea name="body" rows="2" maxlength="1000" placeholder="Notes on this visitor…" class="input textarea"></textarea>
                                <button type="submit" class="btn btn-xs" style="align-self:flex-start;padding:4px 12px;">Submit</button>
                            </form>
                        </details>
                    @endif
                    </div>
                @empty
                    <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);padding:12px;">No visitors yet.</span>
                @endforelse
            </div>
        </div>

    </div>
    </div>

    <x-toast />

    @include('sim.partials.fold-controls')
    @include('sim.partials.responsive')
</x-app-layout>
