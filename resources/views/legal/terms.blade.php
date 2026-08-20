<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terms of Service — {{ config('app.name', 'The Tenth Frame Bowling') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body style="min-height:100vh;background:var(--fog,#f4f4f0);">

    @component('site.partials.core-header')
    @endcomponent

    <div style="max-width:760px;margin:2rem auto;padding:0 1.25rem;">
        <div style="background:var(--sky-light,#eef3f6);border:2px solid var(--navy,#12233f);border-radius:12px;padding:2rem;">
            <h1 style="font-family:var(--font-sub);font-size:1.4rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0 0 0.25rem;">Terms of Service</h1>
            <p style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);letter-spacing:1px;margin-bottom:1.5rem;">LAST UPDATED {{ now()->format('F j, Y') }}</p>

            <div style="font-family:var(--font-body);font-size:0.85rem;color:#333;line-height:1.8;display:flex;flex-direction:column;gap:1rem;">
                <p>By using <strong>The Tenth Frame Bowling</strong>, you agree to these terms.</p>

                <div>
                    <strong>Purpose of the service</strong>
                    <p>This is a bowling club management simulator originally built as a university software engineering project and maintained as a portfolio application. All bookings, payments, staff actions and events within the app are part of a simulated environment.</p>
                </div>

                <div>
                    <strong>Fair use</strong>
                    <p>Do not attempt to disrupt the service, access other users' accounts, or abuse the sign-in system. Accounts may be suspended for misuse.</p>
                </div>

                <div>
                    <strong>No warranty</strong>
                    <p>The service is provided "as is" without warranties of any kind. Features may change or be removed at any time as development continues.</p>
                </div>

                <p>Contact: siamkhandaker616@gmail.com</p>
            </div>
        </div>
    </div>
</body>
</html>
