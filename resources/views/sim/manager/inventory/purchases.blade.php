<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Purchase Bills</h2>
            <div style="display:flex;align-items:center;gap:1rem;">
                @if ($pendingBills->count())
                    <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--gold);">${{ number_format($pendingTotal, 2) }} awaiting approval</span>
                @endif
                <span class="badge-role manager">Manager</span>
            </div>
        </div>
    </x-slot>

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div style="padding:1.25rem;overflow:hidden;">

            @if ($pendingBills->count())
                <div style="margin-bottom:1rem;">
                    @include('sim.partials.bubble', ['type' => 'warn', 'title' => $pendingBills->count() . ' bill' . ($pendingBills->count() > 1 ? 's' : '') . ' waiting on you', 'message' => 'Caretaker restocks are stock-on-shelf but only hit the books once you accept and pay. Rejecting returns the stock; used stock is fined at cost.'])
                </div>
            @endif

            <div class="dash-section-label" style="margin:0 0 10px;">Bill Ledger</div>

            <div style="display:flex;flex-direction:column;gap:10px;">
                @forelse ($bills as $bill)
                    @php
                        $statusColor = $bill->status === 'approved' ? 'var(--ok)' : ($bill->status === 'rejected' ? 'var(--coral)' : 'var(--gold)');
                    @endphp
                    <div style="display:flex;align-items:center;gap:14px;padding:12px 14px;background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;">
                        <div style="flex:1;min-width:180px;">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span style="font-family:var(--font-sub);font-size:0.75rem;">{{ $bill->item_name }}</span>
                                <span class="badge-role" style="background:var(--pin-white);color:{{ $statusColor }};border:2px solid {{ $statusColor }};font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;">{{ $bill->status }}</span>
                                @if ($bill->auto_approved)
                                    <span style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">auto-approved</span>
                                @endif
                            </div>
                            <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);margin-top:3px;">
                                {{ $bill->quantity }} × ${{ $bill->unit_cost }} = ${{ number_format((float) $bill->total, 2) }}
                                @if ($bill->requestedBy)
                                    · requested by {{ $bill->requestedBy->user->name }}
                                @endif
                                · {{ $bill->created_at->format('M j, Y') }}
                            </div>
                            @if ($bill->status === 'rejected' && $bill->fine_amount > 0)
                                @php $consumedUnits = round((float) $bill->fine_amount / max(0.01, (float) $bill->unit_cost)); @endphp
                                <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--coral);margin-top:2px;">fine: ${{ number_format((float) $bill->fine_amount, 2) }} — {{ $consumedUnits }} units were already used when you rejected</div>
                            @endif
                            @if ($bill->reviewedBy)
                                <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);margin-top:2px;">
                                    reviewed by {{ $bill->reviewedBy->user->name }} · {{ $bill->reviewed_at?->format('M j') }}
                                    @if ($bill->payment && $bill->status === 'approved')
                                        · paid <span style="color:var(--ok);">&#10003;</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div style="display:flex;gap:6px;align-items:center;">
                            @if ($bill->isPending())
                                <form method="POST" action="{{ route('manager.inventory.purchases.accept', $bill) }}">
                                    @csrf
                                    <button type="submit" class="btn-lane primary" style="font-size:0.55rem;padding:4px 10px;">Accept & Pay</button>
                                </form>
                                <form method="POST" action="{{ route('manager.inventory.purchases.reject', $bill) }}" onsubmit="return confirm('Reject this bill? Added stock will be returned; used stock is fined.');">
                                    @csrf
                                    <button type="submit" class="btn-lane danger" style="font-size:0.55rem;padding:4px 10px;">Reject</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="text-align:center;padding:2rem;background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;">
                        <span style="font-family:var(--font-mono);font-size:0.7rem;color:var(--slate);">No purchase bills yet — caretaker restocks and adjustments land here for approval.</span>
                    </div>
                @endforelse
            </div>

        </div>
    </div>

    <x-toast />

    @include('sim.partials.fold-controls')
    @include('sim.partials.responsive')
</x-app-layout>
