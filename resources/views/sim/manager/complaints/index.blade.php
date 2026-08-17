<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Complaints & Compensation</h2>

        </div>
    </x-slot>

    <style>
        .comp-card{background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;display:flex;gap:14px;align-items:flex-start;}
        .comp-badge{font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;}
    </style>

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div style="padding:1.25rem;overflow:hidden;">

            <div style="display:flex;flex-direction:column;gap:10px;">
                @forelse ($complaints as $complaint)
                    <div class="comp-card">
                        <div style="flex:1;">
                            <div style="display:flex;gap:8px;align-items:center;">
                                <span class="comp-badge" style="background:{{ match($complaint->status) { 'open' => 'var(--coral-light)', 'investigating' => 'var(--gold-light)', 'resolved' => 'var(--sky)', default => 'var(--mist)' } }};color:var(--navy);border:1px solid var(--navy);">{{ \App\Helpers\Label::complaintStatus($complaint->status) }}</span>
                                <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">{{ \App\Helpers\Label::complaintType($complaint->type) }} · {{ $complaint->created_at->format('M j, H:i') }}</span>
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
                                <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--sky-dark);margin-top:4px;">Resolution: {{ $complaint->resolution }}@if ($complaint->compensation_type) ({{ \App\Helpers\Label::compensationType($complaint->compensation_type) }})@endif</div>
                            @endif
                        </div>

                        @if (in_array($complaint->status, ['open', 'investigating']))
                            <form method="POST" action="{{ route('manager.complaints.resolve', $complaint) }}" class="gutter-form" style="min-width:220px;display:flex;flex-direction:column;gap:6px;">
                                @csrf
                                <textarea name="resolution" placeholder="Resolution..." class="input textarea"></textarea>
                                <div class="select-wrap">
                                    <select name="compensation_type" class="input select">
                                        <option value="">No compensation</option>
                                        <option value="free_game">Free Game</option>
                                        <option value="refund">Refund</option>
                                        <option value="discount">Discount</option>
                                        <option value="apology">Apology</option>
                                        <option value="priority_queue">Priority Queue</option>
                                    </select>
                                    <span class="select-arrow">&#9662;</span>
                                </div>
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

    @include('sim.partials.fold-controls')
    @include('sim.partials.responsive')
</x-app-layout>
