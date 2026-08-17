<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Ban Requests</h2>

        </div>
    </x-slot>

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div style="display:grid;grid-template-columns:220px 1fr;gap:0;">

        <div style="background:var(--sky-light);border-right:3px solid var(--navy);padding:1rem;display:flex;flex-direction:column;">
            <div class="dash-section-label" style="margin-bottom:8px;">Request a Ban</div>
            <form method="POST" action="{{ route('steward.bans.store') }}" class="gutter-form" style="display:flex;flex-direction:column;gap:8px;flex:1;">
                @csrf
                <div class="select-wrap">
                    <select name="visitor_id" class="input select">
                        <option value="" disabled selected>Visitor…</option>
                        @foreach ($visitors as $visitor)
                            <option value="{{ $visitor->id }}">{{ $visitor->name }}</option>
                        @endforeach
                    </select>
                    <span class="select-arrow">&#9662;</span>
                </div>
                <div class="gutter-field">
                    <input name="reason" type="text" placeholder="Reason for ban" class="input">
                    <div class="gutter-err">Reason is required</div>
                    <div class="gutter-flag">&#10003;</div>
                </div>
                <input name="evidence" type="text" placeholder="Evidence (optional)" class="input">
                <div class="lane-stage">
                    <div class="pin-rack">
                        <div class="pin-row"><span class="pin"></span><span class="pin"></span><span class="pin"></span><span class="pin"></span></div>
                        <div class="pin-row"><span class="pin"></span><span class="pin"></span><span class="pin"></span></div>
                        <div class="pin-row"><span class="pin"></span><span class="pin"></span></div>
                        <div class="pin-row"><span class="pin"></span></div>
                    </div>
                    <span class="ball-dot"></span>
                </div>
                <button type="submit" class="btn-lane primary" style="font-size:0.55rem;padding:6px 12px;">Submit Ban Request</button>
            </form>
        </div>

        <div style="padding:1.25rem;overflow:hidden;">
            <div class="dash-section-label" style="margin-bottom:8px;">My Submitted Requests</div>
            <div style="display:flex;flex-direction:column;gap:10px;">
                @forelse ($requests as $request)
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;padding:1rem;display:flex;gap:14px;align-items:flex-start;">
                        <div style="flex:1;">
                            <div style="display:flex;gap:8px;align-items:center;">
                                <span style="font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:{{ match($request->status) { 'pending' => 'var(--gold-light)', 'approved' => 'var(--sky)', default => 'var(--mist)' } }};color:var(--navy);border:1px solid var(--navy);">{{ \App\Helpers\Label::banStatus($request->status) }}</span>
                                <span style="font-family:var(--font-sub);font-size:0.75rem;">{{ $request->visitor->name }}</span>
                                <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">{{ $request->created_at->format('M j, H:i') }}</span>
                            </div>
                            <div style="font-family:var(--font-body);font-size:0.7rem;margin-top:6px;background:var(--pin-white);border-radius:8px;padding:8px 10px;border:1px solid var(--fog);">{{ $request->reason }}</div>
                            @if ($request->evidence)
                                <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--gold-dust);margin-top:4px;">Evidence: {{ $request->evidence }}</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="text-align:center;padding:2rem;">
                        <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">No ban requests yet.</span>
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
