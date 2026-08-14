<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Complaints & Compensation</h2>
            <div style="display:flex;align-items:center;gap:1rem;">
                <span class="badge-role manager">Manager</span>
            </div>
        </div>
    </x-slot>

    <style>
        .comp-card{background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;display:flex;gap:14px;align-items:flex-start;}
        .comp-badge{font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;}
    </style>

    <div style="zoom:1.25;display:grid;grid-template-columns:200px 1fr;gap:0;min-height:calc(100vh - 200px);">

        <div class="dash-sidebar">
            <div class="dash-section-label" style="margin-bottom:4px;">Modules</div>
            <a href="{{ route('manager.dashboard') }}" class="dash-sidebar-link">Overview</a>
            <a href="{{ route('manager.staff.index') }}" class="dash-sidebar-link">Staff</a>
            <a href="{{ route('manager.inventory.index') }}" class="dash-sidebar-link">Inventory</a>
            <a href="{{ route('manager.bookings.index') }}" class="dash-sidebar-link">Bookings</a>
            <a href="{{ route('manager.bans.index') }}" class="dash-sidebar-link">Bans</a>
            <a href="{{ route('manager.complaints.index') }}" class="dash-sidebar-link active">Complaints</a>
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

            <div style="display:flex;flex-direction:column;gap:10px;">
                @forelse ($complaints as $complaint)
                    <div class="comp-card">
                        <div style="flex:1;">
                            <div style="display:flex;gap:8px;align-items:center;">
                                <span class="comp-badge" style="background:{{ match($complaint->status) { 'open' => 'var(--coral-light)', 'investigating' => 'var(--gold-light)', 'resolved' => 'var(--sky)', default => 'var(--mist)' } }};color:var(--navy);border:1px solid var(--navy);">{{ $complaint->status }}</span>
                                <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">{{ $complaint->type }} · {{ $complaint->created_at->format('M j, H:i') }}</span>
                            </div>
                            <div style="font-family:var(--font-body);font-size:0.72rem;color:var(--navy);margin-top:6px;background:var(--pin-white);border-radius:8px;padding:8px 10px;border:1px solid var(--fog);">{{ $complaint->description }}</div>
                            <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);margin-top:6px;">
                                Visitor: {{ $complaint->visitor?->name ?? '—' }}
                                @if ($complaint->staff)
                                    · Staff: {{ $complaint->staff->user->name }}
                                @endif
                                @if ($complaint->raisedBy)
                                    · Raised by: {{ $complaint->raisedBy->user->name }}
                                @endif
                            </div>
                            @if ($complaint->resolution)
                                <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--sky-dark);margin-top:4px;">Resolution: {{ $complaint->resolution }}@if ($complaint->compensation_type) ({{ $complaint->compensation_type }})@endif</div>
                            @endif
                        </div>

                        @if (in_array($complaint->status, ['open', 'investigating']))
                            <form method="POST" action="{{ route('manager.complaints.resolve', $complaint) }}" style="min-width:220px;display:flex;flex-direction:column;gap:6px;">
                                @csrf
                                <textarea name="resolution" placeholder="Resolution..." required style="font-family:var(--font-body);font-size:0.7rem;padding:6px 10px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);"></textarea>
                                <select name="compensation_type" style="font-family:var(--font-body);font-size:0.7rem;padding:5px 8px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                                    <option value="">No compensation</option>
                                    <option value="free_game">Free Game</option>
                                    <option value="refund">Refund</option>
                                    <option value="discount">Discount</option>
                                    <option value="apology">Apology</option>
                                    <option value="priority_queue">Priority Queue</option>
                                </select>
                                <div style="display:flex;gap:6px;">
                                    <button type="submit" class="btn-lane primary" style="flex:1;font-size:0.55rem;padding:5px 10px;">Resolve</button>
                                    <button type="submit" form="dismiss-{{ $complaint->id }}" class="btn-lane secondary" style="flex:1;font-size:0.55rem;padding:5px 10px;">Dismiss</button>
                                </div>
                            </form>
                            <form id="dismiss-{{ $complaint->id }}" method="POST" action="{{ route('manager.complaints.dismiss', $complaint) }}">@csrf</form>
                        @endif
                    </div>
                @empty
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;text-align:center;">
                        <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">No complaints logged.</span>
                    </div>
                @endforelse
            </div>

        </div>
    </div>

    <x-toast />

    @include('sim.partials.responsive')
</x-app-layout>
