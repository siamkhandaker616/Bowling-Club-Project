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
            <a href="{{ route('manager.reviews.index') }}" class="dash-sidebar-link">Reviews</a>
            <a href="{{ route('manager.touring.index') }}" class="dash-sidebar-link active">Touring</a>
            <div style="margin-top:auto;padding-top:0.75rem;border-top:2px solid var(--fog);text-align:center;">
                @php $u = auth()->user(); @endphp
                <div class="ball-avatar ball-sm ball-navy" style="margin:0 auto;"><div class="ball-holes"><span></span><span></span><span></span></div><span class="ball-initials">{{ strtoupper(substr($u->name,0,1)) }}{{ strtoupper(substr(str_replace(' ','',$u->name),-1)) }}</span></div>
                <div style="font-family:var(--font-sub);font-size:0.65rem;margin-top:4px;">{{ ucfirst($u->name) }}</div>
                <span class="badge-role manager" style="font-size:0.5rem;padding:2px 8px;">Manager</span>
            </div>
        </div>

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
                                Arrival {{ $request->arrival_date }} · {{ $request->members }} members · Coordinator: {{ $request->coordinator_name }} · {{ $request->phone }}
                            </div>
                            <div style="font-family:var(--font-body);font-size:0.7rem;margin-top:4px;background:var(--pin-white);border-radius:8px;padding:8px 10px;border:1px solid var(--fog);">{{ $request->notes }}</div>
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
</x-app-layout>
