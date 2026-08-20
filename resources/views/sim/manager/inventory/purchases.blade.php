<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Purchase Bills</h2>
            <div style="display:flex;align-items:center;gap:1rem;">
                @if ($pendingBills->count())
                    <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--gold);">৳{{ number_format($pendingTotal, 2) }} awaiting approval</span>
                @endif

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
                                <span class="badge-role" style="background:var(--pin-white);color:{{ $statusColor }};border:2px solid {{ $statusColor }};font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;">{{ \App\Helpers\Label::billStatus($bill->status) }}</span>
                                @if ($bill->auto_approved)
                                    <span style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">auto-approved</span>
                                @endif
                            </div>
                            <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);margin-top:3px;">
                                {{ $bill->quantity }} × ৳{{ $bill->unit_cost }} = ৳{{ number_format((float) $bill->total, 2) }}
                                @if ($bill->requestedBy)
                                    · requested by {{ $bill->requestedBy->user->name }}
                                @endif
                                · {{ $bill->created_at->format('M j, Y') }}
                            </div>
                            @if ($bill->status === 'rejected' && $bill->fine_amount > 0)
                                @php $consumedUnits = round((float) $bill->fine_amount / max(0.01, (float) $bill->unit_cost)); @endphp
                                <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--coral);margin-top:2px;">fine: ৳{{ number_format((float) $bill->fine_amount, 2) }} — {{ $consumedUnits }} units were already used when you rejected</div>
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
                                <form class="gutter-form" data-accept-form data-accept-url="{{ route('manager.inventory.purchases.accept', $bill) }}" data-payment-url="{{ route('manager.inventory.purchases.status', '__ID__') }}" data-index-url="{{ route('manager.inventory.purchases.index') }}">
                                    @csrf
                                    <input type="hidden" name="purchase_id" value="{{ $bill->id }}">
                                    <button type="submit" class="btn-lane primary" style="font-size:0.55rem;padding:4px 10px;">Accept & Pay</button>
                                </form>
                                <form method="POST" action="{{ route('manager.inventory.purchases.reject', $bill) }}" onsubmit="return confirm('Reject this bill? Added stock will be returned; used stock is fined.');">
                                    @csrf
                                    <button type="submit" class="btn-lane danger" style="font-size:0.55rem;padding:4px 10px;">Reject</button>
                                </form>
                            @elseif ($bill->status === 'approved' && (! $bill->payment || ! $bill->payment->isSuccessful()))
                                <form class="gutter-form" data-accept-form data-accept-url="{{ route('manager.inventory.purchases.pay', $bill) }}" data-payment-url="{{ route('manager.inventory.purchases.status', '__ID__') }}" data-index-url="{{ route('manager.inventory.purchases.index') }}">
                                    @csrf
                                    <button type="submit" class="btn-lane primary" style="font-size:0.55rem;padding:4px 10px;">Pay</button>
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

    <script>
    document.querySelectorAll('[data-accept-form]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = form.querySelector('button[type="submit"]');
            var originalText = btn.textContent;
            btn.textContent = 'Starting...';
            btn.disabled = true;

            fetch(form.dataset.acceptUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.gateway_url) {
                    window.open(data.gateway_url, '_blank');
                    btn.textContent = 'Waiting for payment...';
                    var statusUrl = form.dataset.paymentUrl.replace('__ID__', data.payment_id);
                    var poll = setInterval(function() {
                        fetch(statusUrl).then(function(r) { return r.json(); }).then(function(s) {
                            if (s.successful) {
                                clearInterval(poll);
                                window.location.reload();
                            } else if (s.status === 'failed' || s.status === 'cancelled') {
                                clearInterval(poll);
                                btn.textContent = originalText;
                                btn.disabled = false;
                                showToast('Payment ' + s.status + ' — try again.', 'error');
                            }
                        }).catch(function() {});
                    }, 2000);
                } else {
                    window.location.reload();
                }
            })
            .catch(function() {
                btn.textContent = originalText;
                btn.disabled = false;
                showToast('Could not start payment — try again.', 'error');
            });
        });
    });

    function showToast(msg, type) {
        var el = document.createElement('div');
        el.className = 'toast ' + (type === 'error' ? 'err' : '');
        el.style.cssText = 'position:fixed;bottom:1.4rem;right:1.4rem;z-index:9999;';
        el.innerHTML = '<span class="t-ball"></span><span>' + msg + '</span>';
        document.body.appendChild(el);
        requestAnimationFrame(function() { el.classList.add('show'); });
        setTimeout(function() { el.classList.remove('show'); setTimeout(function() { el.remove(); }, 400); }, 4000);
    }
    </script>

    @include('sim.partials.fold-controls')
    @include('sim.partials.responsive')
</x-app-layout>
