<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'The Tenth Frame Bowling') }} — @yield('title', 'Sign In')</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body style="margin:0;padding:0;background:var(--sky)">
        <div class="auth-split">
            {{-- LEFT PANEL: Bowling atmosphere --}}
            <div class="auth-left">
                <div class="auth-gutter-left"></div>
                <div class="auth-gutter-right"></div>

                <div class="auth-ball">
                    <span class="auth-ball-initials">TTF</span>
                </div>

                <h1 class="auth-brand">The Tenth Frame</h1>
                <p class="auth-tagline">A private bowling club experience</p>

                <div class="auth-lane-stripe"></div>

                <div class="auth-pins">
                    <div class="auth-pin">
                        <div class="auth-pin-head"></div>
                        <div class="auth-pin-neck"></div>
                        <div class="auth-pin-body"></div>
                    </div>
                    <div class="auth-pin">
                        <div class="auth-pin-head"></div>
                        <div class="auth-pin-neck"></div>
                        <div class="auth-pin-body"></div>
                    </div>
                    <div class="auth-pin">
                        <div class="auth-pin-head"></div>
                        <div class="auth-pin-neck"></div>
                        <div class="auth-pin-body"></div>
                    </div>
                </div>
            </div>

            {{-- RIGHT PANEL: Form area --}}
            <div class="auth-right">
                @yield('content')
            </div>
        </div>
    </body>
</html>
