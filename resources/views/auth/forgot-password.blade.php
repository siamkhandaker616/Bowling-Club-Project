@extends('layouts.guest')
@section('title', 'Forgot Password')

@section('content')
<section class="section" style="min-height:100vh;display:flex;flex-direction:column;justify-content:center;">
    <div class="wrap">
        <h2 class="section-title"><span class="pin-dot"></span>Reset your access PIN<span class="pin-dot"></span></h2>

        @if (session('status'))
            <div style="max-width:440px;margin:0 auto 1.2rem;padding:10px 14px;border:2px solid var(--ok);border-radius:8px;background:var(--sky-light);font-family:var(--font-sub);font-size:0.82rem;color:var(--ok);text-align:center">
                {{ session('status') }}
            </div>
        @endif

        <div class="access-panel" style="max-width:440px">
            <div class="access-form">
                <p style="font-family:var(--font-body);font-size:0.85rem;color:var(--slate);text-align:center;line-height:1.55;margin:0 0 1.2rem">
                    Forgot your PIN? Enter your email and we'll send you a reset link.
                </p>

                <form method="POST" action="{{ route('password.email') }}" novalidate id="forgotForm">
                    @csrf
                    <div class="field">
                        <label class="label" for="email">Email <span class="req">*</span></label>
                        <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@example.com">
                        <div class="auth-error" id="emailError"></div>
                    </div>
                    <button type="submit" class="submit" style="margin-top:.6rem">Send Reset Link &rarr;</button>
                </form>

                <p style="font-family:var(--font-mono);font-size:.62rem;color:var(--slate);text-align:center;margin-top:1rem">Remember your password? <a href="{{ route('login') }}">Sign In</a></p>
            </div>
        </div>
    </div>
</section>
@endsection
