@extends('layouts.guest')
@section('title', 'Reset Password')

@section('content')
<div class="auth-form-wrap">

    <div class="auth-logo-row">
        <div class="auth-logo-ball"></div>
        <span class="auth-logo-text">The Tenth Frame</span>
    </div>
    <div class="auth-logo-divider"></div>
    <h2 class="auth-title">New Password</h2>

    <form method="POST" action="{{ route('password.store') }}" novalidate id="resetForm" style="margin-top:1.25rem">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="auth-field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" class="auth-input" placeholder="you@example.com">
            <div class="auth-error" id="emailError"></div>
        </div>

        <div class="auth-field">
            <label for="password">New Password</label>
            <div class="auth-pw-wrap">
                <input id="password" type="password" name="password" required autocomplete="new-password" class="auth-input" placeholder="Enter new password" style="padding-right:42px">
                <button type="button" onclick="togglePwVisibility('password', this)" class="auth-pw-toggle" aria-label="Toggle password visibility">
                    <img src="/images/eye-closed.svg" alt="" width="20" height="20">
                </button>
            </div>
            <div class="auth-error" id="passwordError"></div>
        </div>

        <div class="auth-field">
            <label for="password_confirmation">Confirm Password</label>
            <div class="auth-pw-wrap">
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="auth-input" placeholder="Repeat new password" style="padding-right:42px">
                <button type="button" onclick="togglePwVisibility('password_confirmation', this)" class="auth-pw-toggle" aria-label="Toggle password visibility">
                    <img src="/images/eye-closed.svg" alt="" width="20" height="20">
                </button>
            </div>
            <div class="auth-error" id="password_confirmationError"></div>
        </div>

        <button type="submit" class="auth-submit">Reset Password</button>
    </form>

    <div class="auth-switch">
        Remember your password?
        <a href="{{ route('login') }}">Sign In</a>
    </div>

</div>
@endsection
