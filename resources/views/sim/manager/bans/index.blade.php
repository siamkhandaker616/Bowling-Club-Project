<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Ban Requests</h2>
            <div style="display:flex;align-items:center;gap:1rem;">
                <span class="badge-role manager">Manager</span>
            </div>
        </div>
    </x-slot>

    <style>
        .ban-card{background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;display:flex;gap:14px;align-items:flex-start;}
        .ban-row{display:flex;justify-content:space-between;padding:8px 6px;border-bottom:1px solid var(--fog);align-items:center;}
        .ban-badge{font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;}
    </style>

    <div style="zoom:1.25;display:grid;grid-template-columns:200px 1fr;gap:0;min-height:calc(100vh - 200px);">

        <div class="dash-sidebar">
            <div class="dash-section-label" style="margin-bottom:4px;">Modules</div>
            <a href="{{ route('manager.dashboard') }}" class="dash-sidebar-link">Overview</a>
            <a href="{{ route('manager.staff.index') }}" class="dash-sidebar-link">Staff</a>
            <a href="{{ route('manager.inventory.index') }}" class="dash-sidebar-link">Inventory</a>
            <a href="{{ route('manager.bookings.index') }}" class="dash-sidebar-link">Bookings</a>
            <a href="{{ route('manager.bans.index') }}" class="dash-sidebar-link active">Bans</a>
            <a href="{{ route('manager.complaints.index') }}" class="dash-sidebar-link">Complaints</a>
            <a href="{{ route('manager.confrontations.index') }}" class="dash-sidebar-link">Confrontations</a>
            <a href="{{ route('manager.reviews.index') }}" class="dash-sidebar-link">Reviews</a>
            <a href="{{ route('manager.touring.index') }}" class="dash-sidebar-link">Touring</a>
            <div style="margin-top:auto;padding-top:0.75rem;border-top:2px solid var(--fog);text-align:center;">
                @php $u = auth()->user(); @endphp
                <div class="ball-avatar ball-sm ball-navy" style="margin:0 auto;"><div class="ball-holes"><span></span><span></span><span></span></div><span class="ball-initials">{{ strtoupper(substr($u->name,0,1)) }}{{ strtoupper(substr(str_replace(' ','',$u->name),-1)) }}</span></div>
                <div style="font-family:var(--font-sub);font-size:0.65rem;margin-top:4px;">{{ ucfirst($u->name) }}</div>
                <span class="badge-role manager" style="font-size:0.5rem;padding:2px 8px;">Manager</span>
            </div>
        </div>

        <div style="padding:1.25rem;overflow:hidden;">

            <div class="dash-section-label" style="margin-bottom:8px;">Pending Review</div>
            <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:1.25rem;">
                @forelse ($requests->where('status', 'pending') as $request)
                    <div class="ban-card">
                        <div style="flex:1;">
                            <div style="font-family:var(--font-sub);font-size:0.75rem;">{{ $request->visitor->name }}</div>
                            <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">Requested by {{ $request->requester->user->name }} · {{ $request->created_at->diffForHumans() }}</div>
                            <div style="font-family:var(--font-body);font-size:0.7rem;color:var(--navy);margin-top:6px;background:var(--pin-white);border-radius:8px;padding:8px 10px;border:1px solid var(--fog);">{{ $request->reason }}</div>
                            @if ($request->evidence)
                                <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--gold-dust);margin-top:4px;">Evidence: {{ $request->evidence }}</div>
                            @endif
                        </div>
                        <div style="display:flex;flex-direction:column;gap:6px;min-width:160px;">
                            <form method="POST" action="{{ route('manager.bans.approve', $request) }}">
                                @csrf
                                <input name="notes" type="text" placeholder="Admin notes" style="width:100%;font-family:var(--font-body);font-size:0.65rem;padding:4px 8px;border:2px solid var(--navy);border-radius:6px;margin-bottom:4px;">
                                <button type="submit" class="btn-lane primary" style="width:100%;font-size:0.55rem;padding:5px 10px;">Approve Ban</button>
                            </form>
                            <form method="POST" action="{{ route('manager.bans.deny', $request) }}">
                                @csrf
                                <button type="submit" class="btn-lane secondary" style="width:100%;font-size:0.55rem;padding:5px 10px;">Deny</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;text-align:center;">
                        <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">No pending ban requests.</span>
                    </div>
                @endforelse
            </div>

            <div class="dash-section-label" style="margin-bottom:8px;">Decision History</div>
            <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;margin-bottom:1.25rem;">
                @forelse ($requests->where('status', '!=', 'pending') as $request)
                    <div class="ban-row">
                        <div>
                            <span style="font-family:var(--font-sub);font-size:0.7rem;">{{ $request->visitor->name }}</span>
                            <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);margin-left:8px;">{{ $request->reviewed_at?->format('M j, H:i') }}</span>
                        </div>
                        <span class="ban-badge" style="background:{{ $request->status === 'approved' ? 'var(--sky)' : 'var(--coral-light)' }};color:{{ $request->status === 'approved' ? 'var(--sky-dark)' : 'var(--coral-dark)' }};border:1px solid {{ $request->status === 'approved' ? 'var(--sky-dark)' : 'var(--coral)' }};">{{ $request->status }}</span>
                    </div>
                @empty
                    <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">No decisions yet.</div>
                @endforelse
            </div>

            @if ($banned->count())
                <div class="dash-section-label" style="margin-bottom:8px;">Currently Banned</div>
                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;">
                    @foreach ($banned as $visitor)
                        <div style="display:flex;justify-content:space-between;padding:8px 6px;border-bottom:1px solid var(--fog);">
                            <span style="font-family:var(--font-sub);font-size:0.7rem;">{{ $visitor->name }}</span>
                            <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--coral-dark);">{{ $visitor->ban_reason }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
