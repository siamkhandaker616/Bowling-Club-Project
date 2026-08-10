@extends('layouts.guest')
@section('title', 'Verify Email')

@section('content')
<div class="auth-form-wrap">

    <div class="auth-logo-row">
        <div class="auth-logo-ball"></div>
        <span class="auth-logo-text">The Tenth Frame</span>
    </div>
    <div class="auth-logo-divider"></div>
    <h2 class="auth-title">Verify Email</h2>

    <p style="font-family:var(--font-body);font-size:0.85rem;color:var(--slate);text-align:center;line-height:1.5;margin:1rem 0 0">
        Thanks for signing up! Please verify your email by clicking the link we just sent you.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div style="padding:10px 14px;border:2px solid var(--sky-dark);border-radius:8px;background:var(--sky-light);font-family:var(--font-sub);font-size:0.82rem;color:var(--sky-dark);text-align:center;margin:1rem 0">
            A new verification link has been sent to your email.
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" style="margin-top:1rem">
        @csrf
        <button type="submit" class="auth-submit">Resend Verification Email</button>
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
@endsection
