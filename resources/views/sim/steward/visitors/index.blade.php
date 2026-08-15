<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Visitors</h2>
            <span class="badge-role steward">Steward</span>
        </div>
    </x-slot>

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div style="display:grid;grid-template-columns:220px 1fr 180px;gap:0;">

        <div style="background:var(--sky-light);border-right:3px solid var(--navy);padding:1rem;display:flex;flex-direction:column;">
            <div class="dash-section-label" style="margin-bottom:8px;">Upcoming Check-ins</div>
            <div style="display:flex;flex-direction:column;gap:4px;flex:1;">
                @forelse ($checkIns as $booking)
                    <div style="padding:6px 8px;background:var(--sky);border-radius:6px;border-left:3px solid var(--sky-dark);">
                        <div style="font-family:var(--font-sub);font-size:0.65rem;">{{ $booking->visitor->name }}</div>
                        <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">{{ $booking->time_slot }} · Lane {{ $booking->lane?->lane_number ?? '—' }}</div>
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
                                $ballColor = $visitor->is_banned ? 'ball-coral' : ($visitor->vip ? 'ball-gold' : 'ball-sky');
                            @endphp
                            <div class="ball-avatar ball-sm {{ $ballColor }}"><div class="ball-holes"><span></span><span></span><span></span></div><span class="ball-initials">{{ $initials }}</span></div>
                            <div>
                                <div style="font-family:var(--font-sub);font-size:0.72rem;">{{ $visitor->name }}</div>
                                <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">{{ $visitor->bookings->count() }} visits · rep {{ $visitor->reputation_score }}</div>
                            </div>
                        </div>
                        <div style="display:flex;gap:6px;align-items:center;">
                            @if ($visitor->is_banned)
                                <span style="font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:var(--coral-light);color:var(--coral-dark);border:1px solid var(--coral);">BANNED</span>
                            @else
                                <span style="font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:var(--sky);color:var(--sky-dark);border:1px solid var(--sky-dark);">regular</span>
                            @endif
                            @if ($visitor->vip)
                                <span style="font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:var(--gold-light);color:var(--gold-dust);border:1px solid var(--gold);">VIP</span>
                            @endif
                        </div>
                    </div>
                    @if ($hasReviewable)
                        <details style="padding:0 12px 10px;">
                            <summary style="cursor:pointer;font-family:var(--font-mono);font-size:0.55rem;color:var(--gold-dust);text-transform:uppercase;letter-spacing:1px;">Rate this visitor</summary>
                            <form method="POST" action="{{ route('steward.reviews.store', $visitor) }}" style="display:flex;flex-direction:column;gap:6px;margin-top:8px;padding:10px;background:var(--pin-white);border:1px solid var(--fog);border-radius:8px;">
                                @csrf
                                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                                    <label style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">Rating
                                        <select name="rating" class="fold-select" style="font-family:var(--font-body);font-size:0.7rem;padding:4px 6px;border:2px solid var(--navy);border-radius:6px;background:var(--cloud);margin-left:4px;">
                                            @foreach ([1,2,3,4,5] as $r)
                                                <option value="{{ $r }}">{{ $r }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">Booking
                                        <select name="booking_id" class="fold-select" style="font-family:var(--font-body);font-size:0.7rem;padding:4px 6px;border:2px solid var(--navy);border-radius:6px;background:var(--cloud);margin-left:4px;">
                                            @foreach ($completed as $b)
                                                <option value="{{ $b->id }}">{{ $b->date }} · {{ $b->time_slot }} · L{{ $b->lane?->lane_number ?? '—' }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="pin-check" style="font-family:var(--font-mono);font-size:0.55rem;color:var(--navy);"><input type="checkbox" name="was_polite" value="1"><span class="pin-box"></span> Was polite</label>
                                    <label class="pin-check" style="font-family:var(--font-mono);font-size:0.55rem;color:var(--navy);"><input type="checkbox" name="caused_issues" value="1"><span class="pin-box"></span> Caused issues</label>
                                </div>
                                <textarea name="body" rows="2" maxlength="1000" placeholder="Notes on this visitor…" style="width:100%;padding:6px 8px;border:2px solid var(--fog);border-radius:6px;font-family:var(--font-body);font-size:0.7rem;background:var(--cloud);resize:vertical;"></textarea>
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

        <div style="background:var(--sky-light);border-left:3px solid var(--navy);padding:0.75rem;display:flex;flex-direction:column;gap:6px;align-items:center;">
            <div class="dash-section-label" style="margin-bottom:2px;width:100%;">Quick Actions</div>
            <a href="{{ route('steward.schedule.index') }}" class="shoe-tag" style="width:70%;"><div class="st-shape" style="padding:0.5rem 0.5rem 1rem;"><div class="st-icon" style="font-size:1.2rem;margin:0.4rem 0 0.2rem;">&#128197;</div><h4 style="font-size:0.6rem;">Schedule</h4></div></a>
            <a href="{{ route('steward.bans.index') }}" class="shoe-tag white" style="width:70%;"><div class="st-shape" style="padding:0.5rem 0.5rem 1rem;"><div class="st-icon" style="font-size:1.2rem;margin:0.4rem 0 0.2rem;">&#128683;</div><h4 style="font-size:0.6rem;">Ban Request</h4></div></a>
            <a href="{{ route('steward.complaints.index') }}" class="shoe-tag coral" style="width:70%;"><div class="st-shape" style="padding:0.5rem 0.5rem 1rem;"><div class="st-icon" style="font-size:1.2rem;margin:0.4rem 0 0.2rem;">&#9878;</div><h4 style="font-size:0.6rem;">Complaints</h4></div></a>
            <a href="{{ route('steward.visitors.index') }}" class="shoe-tag" style="width:70%;"><div class="st-shape" style="padding:0.5rem 0.5rem 1rem;"><div class="st-icon" style="font-size:1.2rem;margin:0.4rem 0 0.2rem;">&#128100;</div><h4 style="font-size:0.6rem;">Visitors</h4></div></a>
            <div style="margin-top:auto;padding-top:8px;border-top:2px solid var(--fog);text-align:center;">
                <div class="ball-avatar ball-sm ball-sky" style="margin:0 auto;"><div class="ball-holes"><span></span><span></span><span></span></div><span class="ball-initials">ST</span></div>
                <div style="font-family:var(--font-sub);font-size:0.6rem;margin-top:4px;">{{ ucfirst(auth()->user()->name) }}</div>
                <span class="badge-role steward" style="font-size:0.45rem;padding:2px 6px;">Steward</span>
            </div>
        </div>

    </div>
    </div>

    <x-toast />

    @include('sim.partials.fold-controls')
    @include('sim.partials.responsive')
</x-app-layout>
