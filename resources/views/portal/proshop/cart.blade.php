<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your Bag — The Tenth Frame Pro Shop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body style="min-height:100vh;">

    @component('site.partials.core-header', ['activeRoute' => 'public.proshop.cart'])
    @endcomponent

    <main style="padding:6rem 2rem 4rem;max-width:860px;margin:0 auto;">

        <a href="{{ route('public.proshop.index') }}" style="font-family:var(--font-sub);font-size:0.8rem;color:var(--slate);text-decoration:none;display:inline-block;margin-bottom:1.5rem;">&larr; Back to the Pro Shop</a>

        <h1 style="font-family:var(--font-display);font-size:1.8rem;text-transform:uppercase;color:var(--navy);margin:0 0 0.25rem;">Your Bag</h1>
        <p style="font-family:var(--font-sub);color:var(--slate);font-size:0.9rem;margin:0 0 2rem;">Pay once at checkout — gear is picked up at the front desk.</p>

        @if($cart->isEmpty())
            <div style="text-align:center;padding:4rem 2rem;background:var(--pin-white);border:2px solid var(--navy);border-radius:12px;">
                <div style="font-size:3rem;margin-bottom:1rem;">&#128717;</div>
                <h3 style="font-family:var(--font-header);color:var(--navy);margin-bottom:0.5rem;">Your Bag Is Empty</h3>
                <p style="font-family:var(--font-sub);color:var(--slate);margin-bottom:1.5rem;">Nothing here yet — go grab some gear.</p>
                <a href="{{ route('public.proshop.index') }}" class="btn btn-gold" style="padding:10px 22px;font-size:0.8rem;">Browse the Pro Shop</a>
            </div>
        @else
            <div style="background:var(--pin-white);border:2px solid var(--navy);border-radius:12px;padding:1.5rem 1.5rem 0.5rem;margin-bottom:1.5rem;">
                @foreach($cart as $item)
                    <div class="pub-cart-line" style="display:flex;align-items:center;gap:1rem;padding:1rem 0;border-bottom:1px dashed var(--fog);">
                        <div style="flex:1;min-width:0;">
                            <div style="font-family:var(--font-header);font-size:0.9rem;color:var(--navy);">{{ $item->product?->name ?? 'Unavailable item' }}</div>
                            <div style="font-family:var(--font-mono);font-size:0.7rem;color:var(--slate);">
                                ৳ {{ number_format((float) ($item->product?->price ?? 0), 0) }} &times; {{ $item->quantity }}
                            </div>
                        </div>
                        <div style="font-family:var(--font-mono);font-size:0.85rem;color:var(--navy);font-weight:700;">
                            ৳ {{ number_format((float) ($item->product?->price ?? 0) * $item->quantity, 0) }}
                        </div>
                        <form method="POST" action="{{ route('public.proshop.cart.update') }}" style="display:flex;align-items:center;gap:0.4rem;">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                            <input type="number" name="quantity" value="{{ $item->quantity }}" min="0" max="50" required data-stepper="edit"
                                   style="width:64px;padding:6px 8px;border:2px solid var(--fog);border-radius:8px;font-family:var(--font-mono);font-size:0.8rem;background:var(--cloud);color:var(--navy);outline:none;">
                            <button type="submit" class="btn btn-ghost" style="padding:6px 12px;font-size:0.7rem;">Update</button>
                        </form>
                        <form method="POST" action="{{ route('public.proshop.cart.remove') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                            <button type="submit" class="btn btn-ghost" style="padding:6px 12px;font-size:0.7rem;color:var(--coral);">Remove</button>
                        </form>
                    </div>
                @endforeach

                <div style="display:flex;align-items:center;justify-content:space-between;padding:1.25rem 0 1rem;">
                    <span style="font-family:var(--font-header);font-size:0.9rem;text-transform:uppercase;color:var(--navy);">Total</span>
                    <span style="font-family:var(--font-mono);font-size:1.1rem;color:var(--navy);font-weight:700;">৳ {{ number_format((float) $total, 0) }}</span>
                </div>
            </div>

            <div style="background:var(--pin-white);border:2px solid var(--navy);border-radius:12px;padding:2rem;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;margin-bottom:1.25rem;">
                    <h2 style="font-family:var(--font-header);font-size:0.95rem;text-transform:uppercase;color:var(--navy);margin:0;">Checkout</h2>
                    <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">Pay securely with your card or mobile wallet</span>
                </div>

                <form method="POST" action="{{ route('public.proshop.checkout') }}" novalidate class="gutter-form" data-checkout-form style="display:flex;flex-direction:column;gap:1rem;">
                    @csrf
                    <div class="gutter-field">
                        <label class="label" for="customer_name">Your Name <span class="req">*</span></label>
                        <div class="inp-wrap">
                            <input class="input" id="customer_name" name="customer_name" type="text" value="{{ old('customer_name') }}" required maxlength="120" placeholder="e.g. Samina Chowdhury">
                            <span class="gutter-flag">&#10003;</span>
                        </div>
                        <div class="gutter-err">Name is required</div>
                    </div>
                    <div class="gutter-field">
                        <label class="label" for="customer_email">Email <span class="req">*</span></label>
                        <div class="inp-wrap">
                            <input class="input" id="customer_email" name="customer_email" type="email" value="{{ old('customer_email') }}" required maxlength="255" placeholder="you@example.com">
                            <span class="gutter-flag">&#10003;</span>
                        </div>
                        <div class="gutter-err">Valid email is required</div>
                    </div>
                    <div class="gutter-field">
                        <label class="label" for="customer_phone">Phone (optional)</label>
                        <div class="inp-wrap">
                            <input class="input" id="customer_phone" name="customer_phone" type="text" value="{{ old('customer_phone') }}" maxlength="30" placeholder="01XXXXXXXXX">
                        </div>
                    </div>

                    <div class="lane-stage">
                        <div class="pin-rack">
                            <div class="pin-row"><span class="pin"></span><span class="pin"></span><span class="pin"></span><span class="pin"></span></div>
                            <div class="pin-row"><span class="pin"></span><span class="pin"></span><span class="pin"></span></div>
                            <div class="pin-row"><span class="pin"></span><span class="pin"></span></div>
                            <div class="pin-row"><span class="pin"></span></div>
                        </div>
                        <span class="ball-dot"></span>
                    </div>

                    <button type="submit" class="submit">Proceed to Payment &rarr;</button>
                </form>
            </div>
        @endif

    </main>

    @include('site.partials.core-footer')

    <x-toast />

    <script>
    (function() {
        var form = document.querySelector('[data-checkout-form]');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = form.querySelector('button[type="submit"]');
            var originalText = btn.textContent;
            btn.textContent = 'Processing...';
            btn.disabled = true;

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                },
                body: new FormData(form)
            })
            .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
            .then(function(result) {
                if (!result.ok) {
                    btn.textContent = originalText;
                    btn.disabled = false;
                    if (result.data && result.data.error) {
                        var el = document.createElement('div');
                        el.style.cssText = 'position:fixed;bottom:1.4rem;right:1.4rem;z-index:9999;padding:10px 20px;background:var(--coral);color:#fff;border-radius:8px;font-family:var(--font-sub);font-size:0.85rem;';
                        el.textContent = result.data.error;
                        document.body.appendChild(el);
                        setTimeout(function() { el.remove(); }, 4000);
                    }
                    return;
                }

                var d = result.data;
                if (d.gateway_url) {
                    window.open(d.gateway_url, '_blank');
                    btn.textContent = 'Waiting for payment...';
                    var poll = setInterval(function() {
                        fetch('{{ route("public.pay.status", "__ID__") }}'.replace('__ID__', d.payment_id))
                            .then(function(r) { return r.json(); })
                            .then(function(s) {
                                if (s.successful) {
                                    clearInterval(poll);
                                    window.location = '{{ route("public.pay.success", "__ID__") }}'.replace('__ID__', d.payment_id);
                                } else if (s.status === 'failed' || s.status === 'cancelled') {
                                    clearInterval(poll);
                                    btn.textContent = originalText;
                                    btn.disabled = false;
                                    var el = document.createElement('div');
                                    el.style.cssText = 'position:fixed;bottom:1.4rem;right:1.4rem;z-index:9999;padding:10px 20px;background:var(--coral);color:#fff;border-radius:8px;font-family:var(--font-sub);font-size:0.85rem;';
                                    el.textContent = 'Payment ' + s.status + ' — try again.';
                                    document.body.appendChild(el);
                                    setTimeout(function() { el.remove(); }, 4000);
                                }
                            }).catch(function() {});
                    }, 2000);
                } else if (d.redirect_url) {
                    window.location = d.redirect_url;
                } else {
                    window.location.reload();
                }
            })
            .catch(function() {
                btn.textContent = originalText;
                btn.disabled = false;
            });
        });
    })();
    </script>

    @include('sim.partials.fold-controls')
</body>
</html>
