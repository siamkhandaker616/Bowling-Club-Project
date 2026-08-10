@extends('layouts.guest')
@section('title', 'Create Account')

@section('content')
<div class="auth-form-wrap">

    <div class="auth-logo-row">
        <div class="auth-logo-ball"></div>
        <span class="auth-logo-text">The Tenth Frame</span>
    </div>
    <div class="auth-logo-divider"></div>
    <h2 class="auth-title">Create Account</h2>

    <form method="POST" action="{{ route('register') }}" novalidate id="registerForm" style="margin-top:1.25rem">
        @csrf

        <div class="auth-field">
            <label for="name">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="auth-input" placeholder="Your full name">
            <div class="auth-error" id="nameError"></div>
        </div>

        <div class="auth-field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="auth-input" placeholder="you@example.com">
            <div class="auth-error" id="emailError"></div>
        </div>

        <div class="auth-field">
            <label for="password">Password</label>
            <div class="auth-pw-wrap">
                <input id="password" type="password" name="password" required autocomplete="new-password" class="auth-input" placeholder="Create a password" style="padding-right:42px">
                <button type="button" onclick="togglePwVisibility('password', this)" class="auth-pw-toggle" aria-label="Toggle password visibility">
                    <img src="/images/eye-closed.svg" alt="" width="20" height="20">
                </button>
            </div>
            <div class="auth-pw-strength" id="pwStrength" data-strength="0">
                <div class="auth-pw-pin"></div>
                <div class="auth-pw-pin"></div>
                <div class="auth-pw-pin"></div>
                <div class="auth-pw-pin"></div>
            </div>
            <div class="auth-pw-label" id="pwLabel"></div>
            <div class="auth-error" id="passwordError"></div>
        </div>

        <div class="auth-field">
            <label for="password_confirmation">Confirm Password</label>
            <div class="auth-pw-wrap">
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="auth-input" placeholder="Repeat your password" style="padding-right:42px">
                <button type="button" onclick="togglePwVisibility('password_confirmation', this)" class="auth-pw-toggle" aria-label="Toggle password visibility">
                    <img src="/images/eye-closed.svg" alt="" width="20" height="20">
                </button>
            </div>
            <div class="auth-error" id="password_confirmationError"></div>
        </div>

        <button type="submit" class="auth-submit">Create Account</button>
    </form>

    <div class="auth-or"><span>OR</span></div>

    <a href="{{ route('google.redirect') }}" class="auth-google">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
        </svg>
        Sign up with Google
    </a>

    <div class="auth-switch">
        Already a member?
        <a href="{{ route('login') }}">Sign In</a>
    </div>

</div>
@endsection
