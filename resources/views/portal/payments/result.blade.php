<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment — The Tenth Frame</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body style="min-height:100vh;">

    @component('site.partials.core-header')
    @endcomponent

    <main style="padding:8rem 2rem 4rem;max-width:560px;margin:0 auto;">

        @php
            $order = $payload['order'];
            $booking = $payload['booking'] ?? null;
            $event = $payload['event'];
            $headline = $payload['headline'];
            $emoji = $payload['emoji'];
            $tone = $payload['tone'];
            $copy = $payload['copy'];
            $statusLabel = $payload['status_label'];
        @endphp

        <div style="background:var(--pin-white);border:2px solid var(--navy);border-radius:14px;padding:2.5rem 2rem;text-align:center;box-shadow:var(--shadow-lg);">
            <div style="font-size:3.5rem;line-height:1;margin-bottom:0.75rem;">{!! $emoji !!}</div>
            <h1 style="font-family:var(--font-display);font-size:1.4rem;text-transform:uppercase;color:var(--navy);margin:0 0 0.75rem;">{{ $headline }}</h1>
            <p style="font-family:var(--font-sub);font-size:0.9rem;color:var(--slate);line-height:1.7;margin:0 0 1.5rem;">{{ $copy }}</p>

            <div style="background:var(--mist);border-radius:10px;padding:1.25rem 1.5rem;text-align:left;margin-bottom:1.5rem;">
                @if($order)
                    @foreach($order->items as $line)
                        <div style="display:flex;justify-content:space-between;font-family:var(--font-sub);font-size:0.8rem;color:var(--navy);padding:4px 0;">
                            <span style="color:var(--slate);">{{ $line->product?->name ?? 'Item' }} &times; {{ $line->quantity }}</span>
                            <span style="font-weight:600;">&#2547; {{ number_format((float) $line->unit_price * $line->quantity, 0) }}</span>
                        </div>
                    @endforeach
                    <div style="display:flex;justify-content:space-between;font-family:var(--font-sub);font-size:0.8rem;color:var(--navy);padding:4px 0;border-top:2px solid var(--fog);margin-top:4px;">
                        <span style="color:var(--slate);">Total</span>
                        <span style="font-weight:600;">&#2547; {{ number_format((float) $order->total(), 0) }}</span>
                    </div>
                @if($booking)
                    <div style="display:flex;justify-content:space-between;font-family:var(--font-sub);font-size:0.8rem;color:var(--navy);padding:4px 0;">
                        <span style="color:var(--slate);">Lane</span>
                        <span style="font-weight:600;">Lane {{ $booking->lane?->lane_number ?? '—' }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-family:var(--font-sub);font-size:0.8rem;color:var(--navy);padding:4px 0;">
                        <span style="color:var(--slate);">When</span>
                        <span style="font-weight:600;">{{ $booking->date?->format('M j, Y') }} · {{ $booking->time_slot }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-family:var(--font-sub);font-size:0.8rem;color:var(--navy);padding:4px 0;">
                        <span style="color:var(--slate);">Amount</span>
                        <span style="font-weight:600;">&#2547; {{ number_format((float) $payment->amount, 0) }}</span>
                    </div>
                @else
                    <div style="display:flex;justify-content:space-between;font-family:var(--font-sub);font-size:0.8rem;color:var(--navy);padding:4px 0;">
                        <span style="color:var(--slate);">Event</span>
                        <span style="font-weight:600;">{{ $event?->title ?? '—' }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-family:var(--font-sub);font-size:0.8rem;color:var(--navy);padding:4px 0;">
                        <span style="color:var(--slate);">Amount</span>
                        <span style="font-weight:600;">&#2547; {{ number_format((float) $payment->amount, 0) }}</span>
                    </div>
                @endif
                <div style="display:flex;justify-content:space-between;font-family:var(--font-sub);font-size:0.8rem;color:var(--navy);padding:4px 0;">
                    <span style="color:var(--slate);">Status</span>
                    <span style="font-weight:600;color:{{ $tone }};text-transform:uppercase;">{{ $statusLabel }}</span>
                </div>
                @if($payment->transaction_id)
                <div style="display:flex;justify-content:space-between;font-family:var(--font-sub);font-size:0.8rem;color:var(--navy);padding:4px 0;">
                    <span style="color:var(--slate);">Receipt No.</span>
                    <span style="font-family:var(--font-mono);font-size:0.7rem;">{{ $payment->transaction_id }}</span>
                </div>
                @endif
            </div>

            <div style="display:flex;gap:0.75rem;justify-content:center;flex-wrap:wrap;">
                @if($booking)
                    @if($status !== 'success' && $booking->status === 'pending')
                        <a href="{{ route('visitor.bookings.index') }}" class="btn btn-gold" style="padding:10px 22px;font-size:0.8rem;">Pay Now from My Bookings</a>
                    @else
                        <a href="{{ route('visitor.bookings.index') }}" class="btn btn-gold" style="padding:10px 22px;font-size:0.8rem;">My Bookings</a>
                    @endif
                    <a href="{{ route('visitor.bookings.create') }}" class="btn" style="padding:10px 22px;font-size:0.8rem;">Book Another Lane</a>
                @elseif($order)
                    @if($status !== 'success')
                        <a href="{{ route('public.proshop.cart') }}" class="btn btn-gold" style="padding:10px 22px;font-size:0.8rem;">Retry Checkout</a>
                    @else
                        <a href="{{ route('public.proshop.index') }}" class="btn btn-gold" style="padding:10px 22px;font-size:0.8rem;">Back to the Pro Shop</a>
                    @endif
                @else
                    <a href="{{ route('public.events.show', $event) }}" class="btn btn-gold" style="padding:10px 22px;font-size:0.8rem;">View Event</a>
                @endif
            </div>
        </div>

    </main>

    @include('site.partials.core-footer')

</body>
</html>
