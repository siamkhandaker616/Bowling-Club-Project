<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">My Bookings</h2>

        </div>
    </x-slot>


    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div style="display:flex;flex-wrap:wrap;gap:12px;">
            @forelse ($bookings as $booking)
                <div style="flex:1 1 280px;background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;display:flex;justify-content:space-between;align-items:center;">
                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                        <span style="font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:var(--navy);color:var(--pin-white);">{{ \App\Helpers\Label::timeSlot($booking->time_slot) }}</span>
                        <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">{{ $booking->date->format('M j, Y') }}</span>
                        <span style="font-family:var(--font-sub);font-size:0.75rem;">Lane {{ $booking->lane?->lane_number ?? '—' }}</span>
                        <span style="font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:{{ match($booking->status) { 'confirmed' => 'var(--sky)', 'completed' => 'var(--mist)', 'pending' => 'var(--gold-light)', default => 'var(--coral-light)' } }};color:var(--navy);border:1px solid var(--navy);">{{ \App\Helpers\Label::bookingStatus($booking->status) }}</span>
                        @if ($booking->queue_position)
                            <span style="font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:var(--gold-light);color:var(--gold-dust);border:1px solid var(--gold);">Queue #{{ $booking->queue_position }}</span>
                        @endif
                    </div>
                    @if (in_array($booking->status, ['pending', 'confirmed']))
                        <div style="display:flex;gap:6px;align-items:center;flex-shrink:0;">
                            @if ($booking->status === 'pending' && ! $booking->queue_position && (float) ($booking->amount ?? $price) > 0)
                                <button type="button" class="pay-open btn-lane" style="font-size:0.55rem;padding:5px 12px;"
                                    data-pay-url="{{ route('visitor.bookings.pay', $booking) }}"
                                    data-amount="{{ number_format((float) ($booking->amount ?? $price), 0) }}"
                                    data-desc="Lane {{ $booking->lane?->lane_number ?? '—' }} · {{ $booking->date->format('M j, Y') }} · {{ \App\Helpers\Label::timeSlot($booking->time_slot) }}">
                                    Pay Now
                                </button>
                            @endif
                            <form method="POST" action="{{ route('visitor.bookings.cancel', $booking) }}">
                                @csrf
                                <button type="submit" class="btn-lane secondary" style="font-size:0.55rem;padding:5px 12px;">Cancel</button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;text-align:center;width:100%;">
                    <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">You have no bookings yet. <a href="{{ route('visitor.bookings.create') }}" style="color:var(--sky-dark);">Book a lane</a>.</span>
                </div>
            @endforelse
        </div>
    </div>

    <x-toast />

    <div id="pay-modal" style="display:none;position:fixed;inset:0;background:rgba(26,23,20,.65);z-index:9999;align-items:center;justify-content:center;">
        <div style="background:var(--sky-light);border:3px solid var(--navy);border-radius:16px;box-shadow:8px 8px 0 rgba(38,32,25,.35);max-width:380px;width:92%;padding:1.25rem;position:relative;">
            <button type="button" id="pay-close" style="position:absolute;top:8px;right:12px;background:none;border:none;font-size:1.2rem;cursor:pointer;color:var(--slate);">&times;</button>
            <h3 style="font-family:var(--font-header);color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0 0 .75rem;font-size:.95rem;">Settle Your Lane</h3>

            <div style="border:2px dashed var(--navy);border-radius:10px;padding:.75rem;margin-bottom:.9rem;background:var(--pin-white);">
                <p id="pay-desc" style="font-family:var(--font-sub);font-size:.8rem;color:var(--navy);margin:0 0 .35rem;"></p>
                <p style="font-family:var(--font-mono);font-size:.7rem;color:var(--slate);margin:0;">Amount due:
                    <span id="pay-amount" style="font-weight:bold;color:var(--coral-dark);"></span> BDT
                </p>
            </div>

            <button type="button" id="pay-go" class="btn-lane" style="width:100%;padding:9px 0;">Proceed to Secure Checkout</button>
            <p id="pay-msg" style="display:none;font-family:var(--font-mono);font-size:.68rem;margin:.6rem 0 0;line-height:1.5;"></p>
            <p style="font-family:var(--font-mono);font-size:.6rem;color:var(--slate);margin:.6rem 0 0;text-align:center;">Payments are handled by SSL Commerz in a new tab.</p>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('pay-modal');
            if (!modal) return;

            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const statusBase = '{{ url('visitor/bookings/payment') }}';
            const descEl = document.getElementById('pay-desc');
            const amountEl = document.getElementById('pay-amount');
            const goBtn = document.getElementById('pay-go');
            const msgEl = document.getElementById('pay-msg');
            let payUrl = '';
            let poller = null;

            function say(text, bad) {
                msgEl.style.display = 'block';
                msgEl.textContent = text;
                msgEl.style.color = bad ? 'var(--coral-dark)' : 'var(--sky-dark)';
            }

            function open(btn) {
                payUrl = btn.dataset.payUrl;
                descEl.textContent = btn.dataset.desc;
                amountEl.textContent = btn.dataset.amount;
                msgEl.style.display = 'none';
                goBtn.disabled = false;
                goBtn.textContent = 'Proceed to Secure Checkout';
                modal.style.display = 'flex';
            }

            function close() {
                if (poller) { clearInterval(poller); poller = null; }
                modal.style.display = 'none';
            }

            document.querySelectorAll('.pay-open').forEach(function (btn) {
                btn.addEventListener('click', function () { open(btn); });
            });

            document.getElementById('pay-close').addEventListener('click', close);
            modal.addEventListener('click', function (e) { if (e.target === modal) close(); });

            function startPolling(paymentId) {
                let tries = 0;
                say('Complete the payment in the new tab — waiting for confirmation…');
                poller = setInterval(function () {
                    fetch(statusBase + '/' + paymentId + '/status', { headers: { 'Accept': 'application/json' } })
                        .then(function (r) { return r.json(); })
                        .then(function (s) {
                            if (s.successful) {
                                clearInterval(poller); poller = null;
                                say('Paid! Confirming your lane…');
                                setTimeout(function () { location.reload(); }, 900);
                            } else if (++tries > 45) {
                                clearInterval(poller); poller = null;
                                say('Still not confirmed. If you paid, refresh this page in a moment — otherwise press Pay Now again.', true);
                                goBtn.disabled = false;
                            }
                        })
                        .catch(function () {});
                }, 2000);
            }

            goBtn.addEventListener('click', function () {
                if (!payUrl) return;
                goBtn.disabled = true;
                say('Opening secure checkout…');
                fetch(payUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
                    .then(function (res) {
                        const d = res.data || {};
                        if (d.settled) {
                            say(d.message || 'Payment settled!');
                            setTimeout(function () { location.reload(); }, 900);
                            return;
                        }
                        if (res.ok && d.gateway_url) {
                            window.open(d.gateway_url, '_blank');
                            startPolling(d.payment_id);
                            return;
                        }
                        say(d.error || 'Something went wrong — please try again.', true);
                        goBtn.disabled = false;
                    })
                    .catch(function () {
                        say('Network error — please try again.', true);
                        goBtn.disabled = false;
                    });
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') close();
            });
        })();
    </script>

    @include('sim.partials.responsive')
</x-app-layout>
