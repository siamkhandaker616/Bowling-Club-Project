<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pro Shop — The Tenth Frame</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body style="min-height:100vh;">

    @component('site.partials.core-header', ['activeRoute' => 'public.proshop.index'])
    @endcomponent

    <main style="padding:6rem 2rem 4rem;max-width:1100px;margin:0 auto;">

        <div style="text-align:center;margin-bottom:2.5rem;">
            <h1 style="font-family:var(--font-display);font-size:2.2rem;text-transform:uppercase;color:var(--navy);margin-bottom:0.25rem;">Pro Shop</h1>
            <p style="font-family:var(--font-sub);color:var(--slate);font-size:1rem;">Balls, shoes, towels, and lane gear — grab it before it rolls away.</p>
            <div class="lane-stripe" style="margin:1.5rem auto 0;max-width:400px;"></div>
        </div>

        @php
            $categories = $products->pluck('category')->filter()->unique()->values();
        @endphp

        @if($categories->isNotEmpty())
            <div id="pub-shop-filters" style="display:flex;align-items:center;justify-content:center;gap:0.5rem;flex-wrap:wrap;margin-bottom:2rem;">
                <button type="button" class="pub-cat-chip active" data-cat="all" style="font-family:var(--font-mono);font-size:0.7rem;padding:6px 16px;border:2px solid var(--navy);border-radius:50px;background:var(--navy);color:var(--pin-white);text-transform:uppercase;letter-spacing:1px;cursor:pointer;">All</button>
                @foreach($categories as $category)
                    <button type="button" class="pub-cat-chip" data-cat="{{ $category }}" style="font-family:var(--font-mono);font-size:0.7rem;padding:6px 16px;border:2px solid var(--navy);border-radius:50px;background:var(--cloud);color:var(--navy);text-transform:uppercase;letter-spacing:1px;cursor:pointer;">{{ $category }}</button>
                @endforeach
            </div>
        @endif

        @if($products->isEmpty())
            <div style="text-align:center;padding:4rem 2rem;background:var(--pin-white);border:2px solid var(--navy);border-radius:12px;">
                <div style="font-size:3rem;margin-bottom:1rem;">&#127923;</div>
                <h3 style="font-family:var(--font-header);color:var(--navy);margin-bottom:0.5rem;">Shelves Being Restocked</h3>
                <p style="font-family:var(--font-sub);color:var(--slate);">No gear is listed just yet — check back soon.</p>
            </div>
        @endif

        <div id="pub-shop-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.25rem;">
            @foreach($products as $product)
                @php
                    $soldOut = $product->stock <= 0;
                @endphp
                <div class="pub-product-card" data-cat="{{ $product->category }}" style="background:var(--pin-white);border:2px solid var(--navy);border-radius:12px;overflow:hidden;display:flex;flex-direction:column;transition:transform 0.15s,box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <div style="background:var(--navy);padding:1.5rem 1.25rem;display:flex;align-items:center;justify-content:center;min-height:120px;">
                        <span style="font-size:3rem;line-height:1;">&#127921;</span>
                    </div>
                    <div style="padding:1.25rem;display:flex;flex-direction:column;gap:0.75rem;flex:1;">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
                            <span style="font-family:var(--font-header);font-size:0.95rem;color:var(--navy);">{{ $product->name }}</span>
                            @if($product->category)
                                <span style="font-family:var(--font-mono);font-size:0.6rem;padding:3px 10px;border-radius:50px;background:var(--mist);color:var(--navy);text-transform:uppercase;letter-spacing:1px;white-space:nowrap;">{{ $product->category }}</span>
                            @endif
                        </div>
                        <p style="font-family:var(--font-sub);font-size:0.8rem;color:var(--slate);line-height:1.6;margin:0;">{{ $product->description }}</p>
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:auto;gap:0.75rem;">
                            <span style="font-family:var(--font-mono);font-size:0.85rem;padding:4px 12px;border:2px solid var(--navy);border-radius:50px;background:var(--cloud);color:var(--navy);font-weight:700;">
                                ৳ {{ number_format((float) $product->price, 0) }}
                            </span>
                            <span style="font-family:var(--font-mono);font-size:0.6rem;color:{{ $soldOut ? 'var(--coral)' : 'var(--slate)' }};text-transform:uppercase;letter-spacing:1px;">
                                {{ $soldOut ? 'Sold out' : ($product->stock . ' in stock') }}
                            </span>
                        </div>
                        <button type="button" class="pub-bag-btn btn {{ $soldOut ? '' : 'btn-gold' }}" data-product-id="{{ $product->id }}" data-name="{{ $product->name }}" style="align-self:stretch;" {{ $soldOut ? 'disabled' : '' }}>
                            {{ $soldOut ? 'Sold Out' : 'Add to Bag' }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

    </main>

    @include('site.partials.core-footer')

    <script>
    (function() {
        function pubFireToast(type, msg) {
            var wrap = document.getElementById('pub-toasts');
            if (!wrap) return;
            var t = document.createElement('div');
            t.className = 'toast' + (type === 'err' ? ' err' : '');
            var ball = document.createElement('span');
            ball.className = 't-ball';
            t.appendChild(ball);
            t.appendChild(document.createTextNode(msg));
            wrap.appendChild(t);
            requestAnimationFrame(function() { t.classList.add('show'); });
            setTimeout(function() {
                t.classList.remove('show');
                setTimeout(function() { t.remove(); }, 350);
            }, 3800);
        }

        var chips = document.querySelectorAll('.pub-cat-chip');

        chips.forEach(function(chip) {
            chip.addEventListener('click', function() {
                chips.forEach(function(c) {
                    c.classList.remove('active');
                    c.style.background = 'var(--cloud)';
                    c.style.color = 'var(--navy)';
                });
                chip.classList.add('active');
                chip.style.background = 'var(--navy)';
                chip.style.color = 'var(--pin-white)';

                var cat = chip.dataset.cat;
                document.querySelectorAll('.pub-product-card').forEach(function(card) {
                    card.style.display = (cat === 'all' || card.dataset.cat === cat) ? '' : 'none';
                });
            });
        });

        var buttons = document.querySelectorAll('.pub-bag-btn');

        buttons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var productId = btn.dataset.productId;
                var name = btn.dataset.name;

                fetch('{{ route('public.proshop.cart.add') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ product_id: productId, quantity: 1 })
                })
                .then(function(response) {
                    return response.json().then(function(data) {
                        return { ok: response.ok, data: data };
                    });
                })
                .then(function(result) {
                    if (!result.ok) {
                        pubFireToast('err', result.data && result.data.error ? result.data.error : 'Couldn\'t add that — try again.');
                        return;
                    }
                    var badge = document.getElementById('pub-bag-count');
                    if (badge) {
                        badge.textContent = result.data.cart_count;
                        badge.style.display = 'inline-flex';
                    }
                    pubFireToast('ok', result.data.message || 'Added to your bag!');
                })
                .catch(function() {
                    pubFireToast('err', 'Connection hiccup — give it another roll.');
                });
            });
        });
    })();
    </script>

    <style>
    .pub-bag-btn:disabled { opacity: 0.5; cursor: not-allowed; }
    @media (prefers-reduced-motion: reduce) {
        .pub-product-card { transform: none !important; transition: none !important; }
    }
    </style>

    <x-toast />
    <div class="toast-wrap" id="pub-toasts"></div>

</body>
</html>
