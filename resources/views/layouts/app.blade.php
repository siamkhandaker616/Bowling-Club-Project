<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'The Tenth Frame Bowling') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body style="min-height: 100vh; background: var(--sky);">
        <div style="min-height: 100vh;">
            @include('layouts.navigation')

            @isset($header)
                <header style="background: var(--pin-white); border-bottom: 3px solid var(--navy); box-shadow: var(--shadow-sm);">
                    <div class="wrap" style="padding-top: 1.2rem; padding-bottom: 1.2rem;">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main style="{{ isset($fullWidth) ? 'padding:0;' : 'max-width:1200px;margin:0 auto;padding:2rem;' }}">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
