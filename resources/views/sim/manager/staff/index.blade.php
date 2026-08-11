<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Staff Roster</h2>
            <div style="display:flex;align-items:center;gap:1rem;">
                <span class="badge-role manager">Manager</span>
            </div>
        </div>
    </x-slot>

    <style>
        .sim-happy{height:8px;background:var(--fog);border-radius:4px;overflow:hidden;}
        .sim-happy > div{height:100%;border-radius:4px;}
    </style>

    <div style="zoom:1.25;display:grid;grid-template-columns:200px 1fr;gap:0;min-height:calc(100vh - 200px);">
        <div class="dash-sidebar">
            <div class="dash-section-label" style="margin-bottom:4px;">Modules</div>
            <a href="{{ route('manager.dashboard') }}" class="dash-sidebar-link">Overview</a>
            <a href="{{ route('manager.staff.index') }}" class="dash-sidebar-link active">Staff</a>
            <a href="{{ route('manager.inventory.index') }}" class="dash-sidebar-link">Inventory</a>
            <a href="{{ route('manager.bookings.index') }}" class="dash-sidebar-link">Bookings</a>
            <a href="{{ route('manager.bans.index') }}" class="dash-sidebar-link">Bans</a>
            <a href="{{ route('manager.complaints.index') }}" class="dash-sidebar-link">Complaints</a>
            <a href="{{ route('manager.confrontations.index') }}" class="dash-sidebar-link">Confrontations</a>
            <a href="{{ route('manager.reviews.index') }}" class="dash-sidebar-link">Reviews</a>
            <a href="{{ route('manager.touring.index') }}" class="dash-sidebar-link">Touring</a>
            <div style="margin-top:auto;padding-top:0.75rem;border-top:2px solid var(--fog);text-align:center;">
                <div class="ball-avatar ball-sm ball-navy" style="margin:0 auto;"><div class="ball-holes"><span></span><span></span><span></span></div><span class="ball-initials">SK</span></div>
                <div style="font-family:var(--font-sub);font-size:0.65rem;margin-top:4px;">{{ ucfirst(Auth::user()->name) }}</div>
                <span class="badge-role manager" style="font-size:0.5rem;padding:2px 8px;">Manager</span>
            </div>
        </div>
        <div style="padding:1.25rem;overflow:hidden;">
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:1.25rem;">
                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;padding:10px;text-align:center;">
                    <div style="font-family:var(--font-mono);font-size:1.1rem;font-weight:700;color:var(--navy);">{{ $counts['total'] }}</div>
                    <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">TOTAL STAFF</div>
                </div>
                <div style="background:var(--sky-light);border:2px solid var(--sky-dark);border-radius:10px;padding:10px;text-align:center;">
                    <div style="font-family:var(--font-mono);font-size:1.1rem;font-weight:700;color:var(--sky-dark);">{{ $counts['active'] }}</div>
                    <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">ACTIVE</div>
                </div>
                <div style="background:var(--gold-light);border:2px solid var(--gold);border-radius:10px;padding:10px;text-align:center;">
                    <div style="font-family:var(--font-mono);font-size:1.1rem;font-weight:700;color:var(--gold-dust);">{{ $counts['avg_happiness'] }}</div>
                    <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">AVG HAPPINESS</div>
                </div>
                <div style="background:var(--coral-light);border:2px solid var(--coral);border-radius:10px;padding:10px;text-align:center;">
                    <div style="font-family:var(--font-mono);font-size:1.1rem;font-weight:700;color:var(--coral-dark);">{{ $counts['low_morale'] }}</div>
                    <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">LOW MORALE</div>
                </div>
            </div>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <div class="dash-section-label" style="margin:0;">Active Roster</div>
                <a href="{{ route('manager.staff.create') }}" class="btn-lane primary" style="font-size:0.6rem;padding:5px 14px;">+ Hire Staff</a>
            </div>

            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                @forelse ($staff->where('is_active', true) as $member)
                    <a href="{{ route('manager.staff.show', $member) }}" style="text-decoration:none;color:var(--navy);">
                        <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;padding:12px;display:flex;gap:10px;align-items:center;">
                            <div class="ball-avatar ball-sm ball-{{ $member->role === 'club_manager' ? 'navy' : ($member->role === 'steward' ? 'sky' : 'coral') }}">
                                <div class="ball-holes"><span></span><span></span><span></span></div>
                                <span class="ball-initials">{{ strtoupper(substr($member->user->name, 0, 1)) }}{{ strtoupper(substr(str_replace(' ', '', $member->user->name), -1)) }}</span>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div style="font-family:var(--font-sub);font-size:0.72rem;">{{ $member->user->name }}</div>
                                <span class="badge-role {{ $member->role === 'club_manager' ? 'manager' : ($member->role === 'steward' ? 'steward' : 'caretaker') }}" style="font-size:0.45rem;padding:1px 8px;">{{ $member->role }}</span>
                                <div style="margin-top:6px;">
                                    <div class="sim-happy"><div style="width:{{ $member->happiness }}%;background:{{ $member->happiness < 50 ? 'var(--coral)' : ($member->happiness < 70 ? 'var(--gold)' : 'var(--sky-dark)') }};"></div></div>
                                    <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);margin-top:2px;">Happy {{ $member->happiness }} · Perf {{ $member->performance_score }}</div>
                                </div>
                            </div>
                            <span style="font-family:var(--font-mono);font-size:0.8rem;color:var(--navy);">&#8594;</span>
                        </div>
                    </a>
                @empty
                    <div style="font-family:var(--font-mono);font-size:0.7rem;color:var(--slate);">No active staff yet.</div>
                @endforelse
            </div>

            @if ($staff->where('is_active', false)->count())
                <div class="dash-section-label" style="margin:1.25rem 0 8px;">Former Staff</div>
                <div style="background:var(--pin-white);border:2px solid var(--fog);border-radius:10px;overflow:hidden;">
                    @foreach ($staff->where('is_active', false) as $member)
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 14px;border-bottom:1px solid var(--fog);">
                            <span style="font-family:var(--font-sub);font-size:0.7rem;color:var(--slate);">{{ $member->user->name }}</span>
                            <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--coral-dark);">INACTIVE</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
