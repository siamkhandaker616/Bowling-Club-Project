<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy — {{ config('app.name', 'The Tenth Frame Bowling') }}</title>
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
            <h1 style="font-family:var(--font-sub);font-size:1.4rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0 0 0.25rem;">Privacy Policy</h1>
            <p style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);letter-spacing:1px;margin-bottom:1.5rem;">LAST UPDATED {{ now()->format('F j, Y') }}</p>

            <div style="font-family:var(--font-body);font-size:0.85rem;color:#333;line-height:1.8;display:flex;flex-direction:column;gap:1rem;">
                <p>This policy explains what data <strong>The Tenth Frame Bowling</strong> collects and how it is used.</p>

                <div>
                    <strong>What we collect</strong>
                    <ul style="margin:0.4rem 0 0;padding-left:1.2rem;">
                        <li>Account details you provide directly (name, email address).</li>
                        <li>If you sign in with Google: your Google name, email address and profile avatar, received via Google's OAuth service.</li>
                        <li>Activity within the app, such as lane bookings, reviews, complaints and scores.</li>
                    </ul>
                </div>

                <div>
                    <strong>How we use it</strong>
                    <p>Data is used only to operate the application: creating your account, personalising your dashboard, processing simulated bookings and showing your activity history. We do not sell or share your data with third parties for advertising.</p>
                </div>

                <div>
                    <strong>Google sign-in data</strong>
                    <p>We request only the basic profile scopes (name, email, avatar). We never post to your Google account or access anything beyond what you consent to on the Google sign-in screen. You can revoke access at any time via your <a href="https://myaccount.google.com/permissions" style="color:var(--navy);">Google account permissions page</a>.</p>
                </div>

                <div>
                    <strong>Data removal</strong>
                    <p>You may request deletion of your account and associated data by contacting us; it will be removed from the production database.</p>
                </div>

                <p>Contact: siamkhandaker616@gmail.com</p>
            </div>
        </div>
    </div>
</body>
</html>
