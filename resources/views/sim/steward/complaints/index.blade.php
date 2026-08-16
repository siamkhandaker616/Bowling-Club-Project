<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Complaints</h2>
            <span class="badge-role steward">Steward</span>
        </div>
    </x-slot>

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div style="display:grid;grid-template-columns:220px 1fr;gap:0;">

        <div style="background:var(--sky-light);border-right:3px solid var(--navy);padding:1rem;display:flex;flex-direction:column;">
            <div class="dash-section-label" style="margin-bottom:8px;">Log a Complaint</div>
            <form method="POST" action="{{ route('steward.complaints.store') }}" style="display:flex;flex-direction:column;gap:8px;flex:1;">
                @csrf
                <select name="visitor_id" required class="fold-select" style="font-family:var(--font-body);font-size:0.65rem;padding:6px 8px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                    <option value="" disabled selected>Visitor…</option>
                    @foreach ($visitors as $visitor)
                        <option value="{{ $visitor->id }}">{{ $visitor->name }}</option>
                    @endforeach
                </select>
                <select name="type" required class="fold-select" style="font-family:var(--font-body);font-size:0.65rem;padding:6px 8px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
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

    </div>
    </div>

    <x-toast />

    @include('sim.partials.fold-controls')
    @include('sim.partials.responsive')
</x-app-layout>
