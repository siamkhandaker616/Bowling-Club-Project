<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Welcome, {{ $user->name }}</h2>
            <div style="display:flex;align-items:center;gap:10px;">
                <div class="ball-avatar ball-sm ball-coral"><div class="ball-holes"><span></span><span></span><span></span></div><span class="ball-initials">{{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr($user->name, strrpos($user->name, ' ') + 1, 1)) }}</span></div>

            </div>
        </div>
    </x-slot>

    <style>
        .sim-card{background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;}
    </style>

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.visitor-sidebar')

        <div style="padding:0 1rem;max-width:900px;min-width:0;margin:0 auto;">

        @if (! $visitor)
            <div class="sim-card" style="text-align:center;">
                <span style="font-family:var(--font-mono);font-size:0.7rem;color:var(--slate);">No visitor profile linked to your account yet. The front desk auto-registers walk-in guests — check back on your next visit.</span>
            </div>
        @elseif ($visitor->is_banned)
            <div class="sim-card" style="text-align:center;border-color:var(--coral);">
                <span style="font-family:var(--font-mono);font-size:0.7rem;color:var(--coral-dark);">You are currently banned: {{ $visitor->ban_reason }}</span>
            </div>
        @endif

        <!-- Hero: My Next Booking -->
        <div style="margin-bottom:1.5rem;">
            <div class="dash-section-label" style="margin-bottom:8px;">My Next Booking</div>
            @if($nextBooking)
                <div class="lane-perspective" style="border-radius:12px;">
                    <div style="background:var(--sky-light);border-radius:10px;padding:1.25rem;display:grid;grid-template-columns:1fr auto;gap:1.5rem;align-items:center;border:2px solid var(--navy);">
                        <div>
                            <div style="font-family:var(--font-header);font-size:1.1rem;color:var(--navy);">Lane {{ $nextBooking->lane?->lane_number ?? '?' }} · {{ $nextBooking->date->format('l') }}</div>
                            <div style="font-family:var(--font-sub);font-size:0.85rem;color:var(--slate);margin-top:4px;">{{ $nextBooking->date->format('M j, Y') }} · {{ \App\Helpers\Label::timeSlotFull($nextBooking->time_slot) }}</div>
                            <div style="display:flex;gap:8px;margin-top:10px;align-items:center;">
                                <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">{{ \App\Helpers\Label::bookingStatus($nextBooking->status) }}</span>
                            </div>
                        </div>
                        <div style="text-align:center;">
                            @php
                                $daysAway = \App\Services\Simulation\Clock::date()->diffInDays($nextBooking->date, false);
                                $daysNum = max(0, (int) $daysAway);
                            @endphp
                            <div style="font-family:var(--font-display);font-size:2rem;color:var(--navy);line-height:1;">{{ $daysNum }}</div>
                            <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">{{ $daysNum === 1 ? 'day' : 'days' }} away</div>
                            <a href="{{ route('visitor.bookings.index') }}" class="btn-lane primary" style="font-size:0.6rem;padding:6px 16px;margin-top:8px;display:block;">Manage</a>
                        </div>
                    </div>
                </div>
            @else
                <div class="sim-card" style="text-align:center;">
                    <span style="font-family:var(--font-mono);font-size:0.7rem;color:var(--slate);">No upcoming booking. <a href="{{ route('visitor.bookings.create') }}" style="color:var(--sky-dark);">Book a lane</a>.</span>
                </div>
            @endif
        </div>

        <!-- Two columns: Stats + Events -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

            <!-- My Stats -->
            <div>
                <div class="dash-section-label" style="margin-bottom:8px;">My Activity</div>
                <div style="display:flex;gap:8px;margin-bottom:10px;">
                    <div style="flex:1;text-align:center;padding:8px;background:var(--pin-white);border:2px solid var(--navy);border-radius:8px;">
                        <div style="font-family:var(--font-mono);font-size:1.1rem;font-weight:700;color:var(--navy);">{{ $bookings->count() }}</div>
                        <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">BOOKINGS</div>
                    </div>
                    <div style="flex:1;text-align:center;padding:8px;background:var(--pin-white);border:2px solid var(--navy);border-radius:8px;">
                        <div style="font-family:var(--font-mono);font-size:1.1rem;font-weight:700;color:var(--coral);">{{ $myComplaints }}</div>
                        <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">COMPLAINTS</div>
                    </div>
                    <div style="flex:1;text-align:center;padding:8px;background:var(--pin-white);border:2px solid var(--navy);border-radius:8px;">
                        <div style="font-family:var(--font-mono);font-size:1.1rem;font-weight:700;color:var(--sky-dark);">{{ $myReviews }}</div>
                        <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">REVIEWS</div>
                    </div>
                </div>
                @if($queue)
                    <div style="padding:8px;background:var(--gold-light);border:2px solid var(--gold);border-radius:8px;text-align:center;">
                        <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--navy);">&#128203; In queue — Position {{ $queue->position }}</span>
                    </div>
                @endif
                @if($visitor)
                    <div style="margin-top:8px;padding:8px;background:var(--pin-white);border:2px solid var(--fog);border-radius:8px;text-align:center;">
                        <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">{{ \App\Helpers\Label::tier($visitor->tier) }} Member · Reputation {{ $visitor->reputation_score }}</span>
                    </div>
                @endif
            </div>

            <!-- Upcoming Events -->
            <div>
                <div class="dash-section-label" style="margin-bottom:8px;">Upcoming Events</div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @forelse($events as $event)
                        @php
                            $eventDate = $event->date ? $event->date->format('d') : '--';
                            $eventMonth = $event->date ? $event->date->format('M') : '';
                            $borderColor = $event->price > 0 ? 'var(--gold)' : 'var(--navy)';
                            $bgColor = $event->price > 0 ? 'var(--gold)' : 'var(--coral)';
                        @endphp
                        <div style="background:var(--sky-light);border:2px solid {{ $borderColor }};border-radius:10px;padding:12px;display:flex;gap:12px;align-items:center;">
                            <div style="min-width:48px;text-align:center;padding:6px 4px;background:{{ $bgColor }};border-radius:8px;">
                                <div style="font-family:var(--font-header);font-size:0.9rem;color:{{ $event->price > 0 ? 'var(--navy)' : 'var(--pin-white)' }};line-height:1;">{{ $eventDate }}</div>
                                <div style="font-family:var(--font-mono);font-size:0.45rem;color:{{ $event->price > 0 ? 'var(--navy)' : 'rgba(248,246,240,0.8)' }};">{{ strtoupper($eventMonth) }}</div>
                            </div>
                            <div style="flex:1;">
                                <div style="font-family:var(--font-sub);font-size:0.75rem;">{{ $event->title }}</div>
                                <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">{{ $event->date?->format('M j') }} · {{ $event->venue ?? 'All Lanes' }}</div>
                            </div>
                            @if($event->price > 0)
                                <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--gold);">${{ number_format($event->price, 0) }}</span>
                            @endif
                        </div>
                    @empty
                        <div style="padding:12px;text-align:center;border:2px dashed var(--fog);border-radius:10px;">
                            <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--fog);">No upcoming events</span>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Club News -->
        @if($announcements->count())
            <div style="margin-top:1.5rem;">
                <div class="dash-section-label" style="margin-bottom:6px;">Club News</div>
                <div style="display:flex;flex-direction:column;gap:6px;">
                    @foreach($announcements as $ann)
                        <div class="ball-return"><div class="mini-ball ball-gold"></div><span class="br-text">{{ $ann->title }} — {{ Str::limit($ann->body, 80) }}</span></div>
                    @endforeach
                </div>
            </div>
        @endif

        </div>

    </div>

    <x-toast />

    @include('sim.partials.responsive')
</x-app-layout>
