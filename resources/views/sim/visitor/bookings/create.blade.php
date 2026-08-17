<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Book a Lane</h2>

        </div>
    </x-slot>


    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div style="padding:0 1rem;max-width:900px;margin:0 auto;">

        

        @if (! $visitor)
            <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;text-align:center;">
                <span style="font-family:var(--font-mono);font-size:0.7rem;color:var(--slate);">No visitor profile is linked to your account yet. The front desk auto-registers walk-in guests — check back on your next visit.</span>
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
                        <div class="lc" data-year="{{ $date->year }}">
                            <div class="lc-head">
                                <button type="button" class="lc-nav" aria-label="Previous month">&laquo;</button>
                                <div class="lc-mo"></div>
                                <button type="button" class="lc-nav" aria-label="Next month">&raquo;</button>
                            </div>
                            <div class="lc-frame"><div class="lc-grid"></div></div>
                            <div class="lc-read"><span class="lc-key">Date</span><span class="lc-picked" data-kept="1">{{ $date->format('F j, Y') }}</span></div>
                            <input type="hidden" name="date" class="lc-input" value="{{ $date->toDateString() }}">
                            <input type="hidden" class="lc-m" value="{{ $date->month - 1 }}">
                        </div>
                    </div>
                    <div>
                        <label style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">LANE</label>
                        <div class="br-wrap">
                            <div class="br-trigger" role="button" tabindex="0">
                                <span class="br-ball"></span>
                                <span class="br-val">@if($selectedLaneId) Lane {{ $lanes->firstWhere('id', $selectedLaneId)?->lane_number }} @else Select a lane @endif</span>
                            </div>
                            <div class="br-lane-strip">
                                @foreach ($lanes as $lane)
                                    <div class="br-lane{{ $selectedLaneId === $lane->id ? ' on' : '' }}" data-v="{{ $lane->id }}">Lane {{ $lane->lane_number }}<small>{{ \App\Helpers\Label::laneStatus($lane->status) }}</small></div>
                                @endforeach
                            </div>
                            <input type="hidden" name="lane_id" value="{{ $selectedLaneId ?? '' }}">
                        </div>
                    </div>
                    <div>
                        <label style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">TIME SLOT</label>
                        <select name="time_slot" class="input select" style="width:100%;">
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
    </div>

    <x-toast />

    @include('sim.partials.fold-controls')
    @include('sim.partials.responsive')
</x-app-layout>
