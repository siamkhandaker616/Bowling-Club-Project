<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'The Tenth Frame Bowling') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    </head>
    <body style="min-height: 100vh;">
        <div style="min-height: 100vh;">
            @include('layouts.navigation')

            @isset($header)
                <header style="background: var(--pin-white); border-bottom: 3px solid var(--navy); box-shadow: var(--shadow-sm);">
                    <div style="max-width: 1200px; margin: 0 auto; padding: 1.5rem 2rem;">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main style="max-width: {{ isset($fullWidth) ? 'none' : '1200px' }}; margin: 0 auto; padding: {{ isset($fullWidth) ? '0' : '2rem' }};">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
