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

    @component('site.partials.core-header')
        <a href="{{ route('public.events') }}" class="btn btn-ghost" style="padding:8px 20px;font-size:0.8rem;">Events</a>
        <a href="{{ route('public.proshop.index') }}" class="btn btn-ghost" style="padding:8px 20px;font-size:0.8rem;">Pro Shop</a>
        <a href="{{ route('public.proshop.cart') }}" class="btn btn-coral" style="padding:8px 20px;font-size:0.8rem;">Bag</a>
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

                <form method="POST" action="{{ route('public.proshop.checkout') }}" style="display:flex;flex-direction:column;gap:1rem;">
                    @csrf
                    <div>
                        <label for="customer_name" style="display:block;font-family:var(--font-sub);font-size:0.8rem;color:var(--slate);margin-bottom:5px;">Your Name *</label>
                        <input id="customer_name" name="customer_name" type="text" value="{{ old('customer_name') }}" required maxlength="120" placeholder="e.g. Samina Chowdhury"
                               style="width:100%;padding:10px 14px;border:2px solid var(--fog);border-radius:8px;font-family:var(--font-body);font-size:0.9rem;background:var(--cloud);color:var(--navy);outline:none;">
                    </div>
                    <div>
                        <label for="customer_email" style="display:block;font-family:var(--font-sub);font-size:0.8rem;color:var(--slate);margin-bottom:5px;">Email *</label>
                        <input id="customer_email" name="customer_email" type="email" value="{{ old('customer_email') }}" required maxlength="255" placeholder="you@example.com"
                               style="width:100%;padding:10px 14px;border:2px solid var(--fog);border-radius:8px;font-family:var(--font-body);font-size:0.9rem;background:var(--cloud);color:var(--navy);outline:none;">
                    </div>
                    <div>
                        <label for="customer_phone" style="display:block;font-family:var(--font-sub);font-size:0.8rem;color:var(--slate);margin-bottom:5px;">Phone (optional)</label>
                        <input id="customer_phone" name="customer_phone" type="text" value="{{ old('customer_phone') }}" maxlength="30" placeholder="01XXXXXXXXX"
                               style="width:100%;padding:10px 14px;border:2px solid var(--fog);border-radius:8px;font-family:var(--font-mono);font-size:0.85rem;background:var(--cloud);color:var(--navy);outline:none;">
                    </div>

                    @if($errors->any())
                        <div style="background:var(--coral-light);border:2px solid var(--coral);border-radius:8px;padding:1rem 1.5rem;font-family:var(--font-sub);font-size:0.85rem;color:var(--coral-dark);">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <button type="submit" class="btn btn-gold" style="align-self:flex-start;">Proceed to Payment</button>
                </form>
            </div>
        @endif

    </main>

    @include('site.partials.core-footer')

    <x-toast />

    @include('sim.partials.fold-controls')
</body>
</html>
