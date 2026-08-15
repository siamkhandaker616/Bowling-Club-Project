<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Touring Teams</h2>
            <div style="display:flex;align-items:center;gap:1rem;">
                <span class="badge-role manager">Manager</span>
            </div>
        </div>
    </x-slot>

    <style>
        .tour-card{background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;display:flex;gap:14px;align-items:center;}
        .tour-badge{font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;}
    </style>

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div style="padding:1.25rem;overflow:hidden;">

            <div class="dash-section-label" style="margin-bottom:8px;">Touring Requests</div>
            <div style="display:flex;flex-direction:column;gap:10px;">
                @forelse ($requests as $request)
                    <div class="tour-card">
                        <div style="flex:1;">
                            <div style="display:flex;gap:8px;align-items:center;">
                                <span class="tour-badge" style="background:{{ match($request->status) { 'pending' => 'var(--gold-light)', 'confirmed' => 'var(--sky)', default => 'var(--mist)' } }};color:var(--navy);border:1px solid var(--navy);">{{ $request->status }}</span>
                                <span style="font-family:var(--font-header);font-size:0.85rem;color:var(--navy);text-transform:uppercase;">{{ $request->team_name }}</span>
                            </div>
                            <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);margin-top:4px;">
                                Arrival {{ $request->arrival_date->format('M j, Y') }} · {{ $request->player_count }} players · {{ $request->home_club ?: 'Home club n/a' }}
                            </div>
                            @if ($request->message)
                            <div style="font-family:var(--font-body);font-size:0.7rem;margin-top:4px;background:var(--pin-white);border-radius:8px;padding:8px 10px;border:1px solid var(--fog);">{{ $request->message }}</div>
                            @endif
                        </div>
                        @if ($request->status === 'pending')
                            <div style="display:flex;flex-direction:column;gap:6px;min-width:130px;">
                                <form method="POST" action="{{ route('manager.touring.confirm', $request) }}">
                                    @csrf
                                    <button type="submit" class="btn-lane primary" style="width:100%;font-size:0.55rem;padding:5px 10px;">Confirm</button>
                                </form>
                                <form method="POST" action="{{ route('manager.touring.decline', $request) }}">
                                    @csrf
                                    <button type="submit" class="btn-lane secondary" style="width:100%;font-size:0.55rem;padding:5px 10px;">Decline</button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;text-align:center;">
                        <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">No touring requests yet.</span>
                    </div>
                @endforelse
            </div>

        </div>
    </div>

    <x-toast />

    @include('sim.partials.responsive')
</x-app-layout>
