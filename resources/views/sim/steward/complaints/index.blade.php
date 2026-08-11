<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Complaints</h2>
            <span class="badge-role steward">Steward</span>
        </div>
    </x-slot>

    <div style="zoom:1.25;display:grid;grid-template-columns:220px 1fr 180px;gap:0;min-height:calc(100vh - 200px);">

        <div style="background:var(--sky-light);border-right:3px solid var(--navy);padding:1rem;display:flex;flex-direction:column;">
            <div class="dash-section-label" style="margin-bottom:8px;">Log a Complaint</div>
            <form method="POST" action="{{ route('steward.complaints.store') }}" style="display:flex;flex-direction:column;gap:8px;flex:1;">
                @csrf
                <select name="visitor_id" required style="font-family:var(--font-body);font-size:0.65rem;padding:6px 8px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                    <option value="" disabled selected>Visitor…</option>
                    @foreach ($visitors as $visitor)
                        <option value="{{ $visitor->id }}">{{ $visitor->name }}</option>
                    @endforeach
                </select>
                <select name="type" required style="font-family:var(--font-body);font-size:0.65rem;padding:6px 8px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                    <option value="service">Service</option>
                    <option value="cleanliness">Cleanliness</option>
                    <option value="behavior">Behavior</option>
                    <option value="facility">Facility</option>
                    <option value="other">Other</option>
                </select>
                <input name="description" type="text" placeholder="Description" required style="font-family:var(--font-body);font-size:0.65rem;padding:6px 8px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                <button type="submit" class="btn-lane primary" style="font-size:0.55rem;padding:6px 12px;">Log Complaint</button>
            </form>
        </div>

        <div style="padding:1.25rem;overflow:hidden;">
            <div class="dash-section-label" style="margin-bottom:8px;">All Complaints</div>
            <div style="display:flex;flex-direction:column;gap:10px;">
                @forelse ($complaints as $complaint)
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;padding:1rem;display:flex;gap:14px;align-items:flex-start;">
                        <div style="flex:1;">
                            <div style="display:flex;gap:8px;align-items:center;">
                                <span style="font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:{{ match($complaint->status) { 'open' => 'var(--coral-light)', 'investigating' => 'var(--gold-light)', 'resolved' => 'var(--sky)', default => 'var(--mist)' } }};color:var(--navy);border:1px solid var(--navy);">{{ $complaint->status }}</span>
                                <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">{{ $complaint->type }} · {{ $complaint->created_at->format('M j, H:i') }}</span>
                            </div>
                            <div style="font-family:var(--font-body);font-size:0.7rem;color:var(--navy);margin-top:6px;background:var(--pin-white);border-radius:8px;padding:8px 10px;border:1px solid var(--fog);">{{ $complaint->description }}</div>
                            <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);margin-top:6px;">Visitor: {{ $complaint->visitor?->name ?? '—' }}</div>
                            @if ($complaint->resolution)
                                <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--sky-dark);margin-top:4px;">Resolution: {{ $complaint->resolution }}@if ($complaint->compensation_type) ({{ $complaint->compensation_type }})@endif</div>
                            @endif
                        </div>
                        @if (in_array($complaint->status, ['open', 'investigating']))
                            <form method="POST" action="{{ route('steward.complaints.escalate', $complaint) }}">
                                @csrf
                                <button type="submit" class="btn-lane secondary" style="font-size:0.55rem;padding:5px 12px;">Escalate</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div style="text-align:center;padding:2rem;">
                        <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">No complaints.</span>
                    </div>
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
                <div style="font-family:var(--font-sub);font-size:0.6rem;margin-top:4px;">{{ ucfirst($user->name ?? Auth::user()->name) }}</div>
                <span class="badge-role steward" style="font-size:0.45rem;padding:2px 6px;">Steward</span>
            </div>
        </div>

    </div>
</x-app-layout>
