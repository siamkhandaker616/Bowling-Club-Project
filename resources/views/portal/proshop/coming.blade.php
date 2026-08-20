<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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

    <main style="padding:8rem 2rem 4rem;max-width:640px;margin:0 auto;text-align:center;">

        <div style="background:var(--pin-white);border:2px solid var(--navy);border-radius:14px;padding:3rem 2rem;box-shadow:var(--shadow-lg);">
            <div style="font-size:3.5rem;line-height:1;margin-bottom:0.75rem;">&#128730;</div>
            <h1 style="font-family:var(--font-display);font-size:1.6rem;text-transform:uppercase;color:var(--navy);margin:0 0 0.75rem;">Pro Shop — Opening Soon</h1>
            <p style="font-family:var(--font-sub);font-size:0.9rem;color:var(--slate);line-height:1.7;margin:0 0 1.5rem;">
                The shelves are being stocked. Balls, shoes, towels, and lane gear land here shortly.
            </p>
            <a href="{{ route('public.events') }}" class="btn btn-gold" style="padding:10px 22px;font-size:0.8rem;">Back to the Events Hub</a>
        </div>

    </main>

    @include('site.partials.core-footer')

    <x-toast />

</body>
</html>
