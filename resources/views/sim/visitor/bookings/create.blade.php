<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Book a Lane</h2>

        </div>
    </x-slot>


    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div style="padding:0 1rem;max-width:900px;margin:0 auto;">

        

        @if (! $visitor)
            <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;text-align:center;">
                <span style="font-family:var(--font-mono);font-size:0.7rem;color:var(--slate);">No visitor profile is linked to your account yet. The front desk auto-registers walk-in guests — check back on your next visit.</span>
            </div>
        @elseif ($visitor->is_banned)
            <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;text-align:center;">
                <span style="font-family:var(--font-mono);font-size:0.7rem;color:var(--coral-dark);">You are currently banned from booking lanes.</span>
            </div>
        @else
            <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;">
                <div class="dash-section-label">New Reservation</div>
                @if($bookingPrice > 0)
                    <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--navy);margin-bottom:8px;background:var(--pin-white);border:1px solid var(--fog);border-radius:6px;padding:6px 10px;display:inline-block;">Booking fee: &#2547;{{ number_format($bookingPrice, 2) }}</div>
                @endif
                <form method="POST" action="{{ route('visitor.bookings.store') }}" novalidate class="gutter-form" data-booking-form style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:12px;margin-top:10px;">
                    @csrf
                    <div class="gutter-field field">
                        <label class="label" for="bk-date">DATE <span class="req">*</span></label>
                        <div class="inp-wrap">
                            <input id="bk-date" type="date" name="date" data-datepicker class="input{{ $errors->has('date') ? ' bad' : '' }}" value="{{ $date->toDateString() }}">
                            <span class="gutter-flag">&#10003;</span>
                        </div>
                        <div class="gutter-err">@error('date'){{ $message }}@else Date is required @enderror</div>
                    </div>
                    <div class="gutter-field field">
                        <label class="label">LANE <span class="req">*</span></label>
                        <div class="br-wrap">
                            <div class="br-trigger" role="button" tabindex="0">
                                <span class="br-ball"></span>
                                <span class="br-val">@if($selectedLaneId) Lane {{ $lanes->firstWhere('id', $selectedLaneId)?->lane_number }} @else Select a lane @endif</span>
                            </div>
                            <div class="br-lane-strip">
                                @foreach ($lanes as $lane)
                                    <div class="br-lane{{ $selectedLaneId === $lane->id ? ' on' : '' }}" data-v="{{ $lane->id }}">Lane {{ $lane->lane_number }}<small>{{ \App\Helpers\Label::laneStatus($lane->status) }}</small></div>
                                @endforeach
                            </div>
                            <input type="hidden" name="lane_id" data-validate value="{{ $selectedLaneId ?? '' }}">
                        </div>
                        <div class="gutter-err">@error('lane_id'){{ $message }}@else Select a lane @enderror</div>
                    </div>
                    <div class="gutter-field field">
                        <label class="label" for="bk-slot">TIME SLOT <span class="req">*</span></label>
                        <div class="inp-wrap">
                            <select id="bk-slot" name="time_slot" class="input fold-select{{ $errors->has('time_slot') ? ' bad' : '' }}" style="width:100%;">
                                @foreach ($slots as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <span class="gutter-flag">&#10003;</span>
                        </div>
                        <div class="gutter-err">@error('time_slot'){{ $message }}@else Pick a time slot @enderror</div>
                    </div>
                    <div class="lane-stage" style="align-self:end;">
                        <div class="pin-rack">
                            <div class="pin-row"><span class="pin"></span><span class="pin"></span><span class="pin"></span><span class="pin"></span></div>
                            <div class="pin-row"><span class="pin"></span><span class="pin"></span><span class="pin"></span></div>
                            <div class="pin-row"><span class="pin"></span><span class="pin"></span></div>
                            <div class="pin-row"><span class="pin"></span></div>
                        </div>
                        <span class="ball-dot"></span>
                    </div>
                    <div style="align-self:end;">
                        <button type="submit" class="btn-lane primary" style="padding:8px 18px;font-size:0.6rem;">{{ $bookingPrice > 0 ? 'Pay & Book' : 'Book' }}</button>
                    </div>
                </form>
                <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);margin-top:10px;">If the lane is already taken for that slot, you'll join the waiting queue automatically.</div>
            </div>
        @endif

    </div>
    </div>

    <x-toast />

    @if($bookingPrice > 0)
    <script>
    (function() {
        var form = document.querySelector('[data-booking-form]');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            var laneInput = form.querySelector('input[name="lane_id"]');
            if (!laneInput || !laneInput.value) {
                gutterToast('Select a lane before booking.');
                return;
            }

            var btn = form.querySelector('button[type="submit"]');
            var originalText = btn.textContent;
            btn.textContent = 'Processing...';
            btn.disabled = true;

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(form)
            })
            .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
            .then(function(result) {
                if (!result.ok) {
                    btn.textContent = originalText;
                    btn.disabled = false;
                    var msg = (result.data && result.data.error) ? result.data.error : 'Something went wrong — try again.';
                    gutterToast(msg);
                    return;
                }

                var d = result.data;

                if (d.redirect) {
                    window.location = d.redirect;
                    return;
                }

                if (d.gateway_url) {
                    window.open(d.gateway_url, '_blank');
                    btn.textContent = 'Waiting for payment...';
                    var poll = setInterval(function() {
                        fetch('{{ route("visitor.bookings.payment.status", "__ID__") }}'.replace('__ID__', d.payment_id))
                            .then(function(r) { return r.json(); })
                            .then(function(s) {
                                if (s.successful) {
                                    clearInterval(poll);
                                    window.location = d.redirect || '{{ route("visitor.bookings.index") }}';
                                } else if (s.status === 'failed' || s.status === 'cancelled') {
                                    clearInterval(poll);
                                    btn.textContent = originalText;
                                    btn.disabled = false;
                                    gutterToast('Payment ' + s.status + ' — try again.');
                                }
                            }).catch(function() {});
                    }, 2000);
                } else {
                    window.location = d.redirect || '{{ route("visitor.bookings.index") }}';
                }
            })
            .catch(function() {
                btn.textContent = originalText;
                btn.disabled = false;
                gutterToast('Connection error — try again.');
            });
        });
    })();
    </script>
    @endif

    @include('sim.partials.fold-controls')
    @include('sim.partials.responsive')
</x-app-layout>
