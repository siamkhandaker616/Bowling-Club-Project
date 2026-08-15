@extends('layouts.guest')
@section('title', 'Verify Email')

@section('content')
<section class="section" style="min-height:100vh;display:flex;flex-direction:column;justify-content:center;">
    <div class="wrap">
        <h2 class="section-title"><span class="pin-dot"></span>Verify Email<span class="pin-dot"></span></h2>

        <div class="access-panel" style="max-width:440px">
            <div class="access-form">
                <p style="font-family:var(--font-body);font-size:0.85rem;color:var(--slate);text-align:center;line-height:1.55;margin:0 0 1.2rem">
                    Thanks for signing up! Please verify your email by clicking the link we just sent you.
                </p>

                @if (session('status') == 'verification-link-sent')
                    <div style="padding:10px 14px;border:2px solid var(--ok);border-radius:8px;background:var(--sky-light);font-family:var(--font-sub);font-size:0.82rem;color:var(--ok);text-align:center;margin:0 0 1rem">
                        A new verification link has been sent to your email.
                    </div>
                @endif

                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="submit">Resend Verification Email &rarr;</button>
                </form>

                <div style="text-align:center;margin-top:1.25rem">
                    <form method="POST" action="{{ route('logout') }}" style="display:inline">
                        @csrf
                        <button type="submit" style="font-family:var(--font-sub);font-size:0.8rem;color:var(--slate);background:none;border:none;cursor:pointer;text-decoration:underline;padding:0" onmouseover="this.style.color='var(--navy)'" onmouseout="this.style.color='var(--slate)'">
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
