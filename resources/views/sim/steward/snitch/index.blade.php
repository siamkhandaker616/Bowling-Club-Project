<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Snitch Inbox</h2>

        </div>
    </x-slot>

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div style="display:flex;flex-direction:column;gap:1.2rem;">
            <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
                    <span class="dash-section-label" style="margin:0;">Waiting on Your Desk</span>
                    <span class="badge coral">{{ $pending->count() }} REPORTS</span>
                </div>

                <div style="display:flex;flex-direction:column;gap:0.9rem;">
                    @forelse ($pending as $report)
                        <div style="border:2px solid var(--navy);border-radius:12px;padding:1rem;background:var(--pin-white);">
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                                <span style="font-family:var(--font-sub);font-size:0.8rem;color:var(--navy);font-weight:700;">
                                    {{ $report->reporter->user->name ?? 'Caretaker' }} <span style="color:var(--slate);font-weight:400;">&#8594; reported</span> {{ $report->accused->user->name ?? 'a coworker' }}
                                </span>
                                <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">{{ $report->created_at->format('M j, H:i') }}</span>
                            </div>
                            <div style="font-family:var(--font-body);font-size:0.75rem;margin-top:0.5rem;background:var(--sky-light);border-radius:8px;padding:0.6rem 0.75rem;border:1px solid var(--fog);">
                                Overheard: &ldquo;{{ $report->quote ?? 'trash-talking management' }}&rdquo;
                            </div>
                            <div style="display:flex;gap:0.6rem;margin-top:0.75rem;flex-wrap:wrap;">
                                <form method="POST" action="{{ route('steward.snitch.escalate', $report) }}" class="gutter-form">
                                    @csrf
                                    <input name="note" type="text" placeholder="Optional steward note" maxlength="300" class="input" style="margin-right:0.4rem;">
                                    <button type="submit" class="btn btn-xs">Escalate to Manager &#8594;</button>
                                </form>
                                <form method="POST" action="{{ route('steward.snitch.dismiss', $report) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-ghost btn-xs">Dismiss</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center;padding:1.5rem;">
                            <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">Desk is clear. No snitch reports waiting.</span>
                        </div>
                    @endforelse
                </div>
            </div>

            <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
                    <span class="dash-section-label" style="margin:0;">Snitch Ledger</span>
                    <span class="badge sky">HANDLED</span>
                </div>

                <div style="display:flex;flex-direction:column;gap:0.6rem;">
                    @forelse ($recent as $report)
                        <div style="display:flex;align-items:center;gap:0.5rem;border-bottom:1px dashed var(--fog);padding:0.6rem 0.2rem;">
                            <span style="font-family:var(--font-sub);font-size:0.74rem;color:var(--navy);font-weight:700;min-width:150px;">{{ $report->reporter->user->name ?? 'Caretaker' }} &#8594; {{ $report->accused->user->name ?? 'Coworker' }}</span>
                            <span class="badge {{ match($report->status) { 'escalated' => 'sky', 'resolved' => 'ok', default => 'coral' } }}" style="font-size:0.52rem;">{{ \App\Helpers\Label::complaintStatus($report->status) }}</span>
                            <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">{{ $report->created_at->format('M j') }}</span>
                            @if ($report->confrontation && $report->confrontation->manager_verdict)
                                <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--gold-dust);">verdict: {{ \App\Helpers\Label::confrontationVerdict($report->confrontation->manager_verdict) }}</span>
                            @endif
                        </div>
                    @empty
                        <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">Nothing handled yet.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <x-toast />

    @include('sim.partials.responsive')
</x-app-layout>
