@extends('layouts.guest')
@section('title', 'Forgot Password')

@section('content')
<div class="auth-form-wrap">

    <div class="auth-logo-row">
        <div class="auth-logo-ball"></div>
        <span class="auth-logo-text">The Tenth Frame</span>
    </div>
    <div class="auth-logo-divider"></div>
    <h2 class="auth-title">Reset Password</h2>

    <p style="font-family:var(--font-body);font-size:0.85rem;color:var(--slate);text-align:center;line-height:1.5;margin:1rem 0 0">
        Forgot your password? Enter your email and we'll send you a reset link.
    </p>

    @if (session('status'))
        <div style="padding:10px 14px;border:2px solid var(--sky-dark);border-radius:8px;background:var(--sky-light);font-family:var(--font-sub);font-size:0.82rem;color:var(--sky-dark);text-align:center;margin:1rem 0">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" novalidate id="forgotForm" style="margin-top:1rem">
        @csrf

        <div class="auth-field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="auth-input" placeholder="you@example.com">
            <div class="auth-error" id="emailError"></div>
        </div>

        <button type="submit" class="auth-submit">Send Reset Link</button>
    </form>

    <div class="auth-switch">
        Remember your password?
        <a href="{{ route('login') }}">Sign In</a>
    </div>

</div>
@endsection
