@extends('layouts.guest')
@section('title', 'Confirm Password')

@section('content')
<div class="auth-form-wrap">

    <div class="auth-logo-row">
        <div class="auth-logo-ball"></div>
        <span class="auth-logo-text">The Tenth Frame</span>
    </div>
    <div class="auth-logo-divider"></div>
    <h2 class="auth-title">Confirm Password</h2>

    <p style="font-family:var(--font-body);font-size:0.85rem;color:var(--slate);text-align:center;line-height:1.5;margin:1rem 0 0">
        This is a secure area. Please confirm your password before continuing.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}" novalidate id="confirmForm" style="margin-top:1rem">
        @csrf

        <div class="auth-field">
            <label for="password">Password</label>
            <div class="auth-pw-wrap">
                <input id="password" type="password" name="password" required autocomplete="current-password" class="auth-input" placeholder="Enter your password" style="padding-right:42px">
                <button type="button" onclick="togglePwVisibility('password', this)" class="auth-pw-toggle" aria-label="Toggle password visibility">
                    <img src="/images/eye-closed.svg" alt="" width="20" height="20">
                </button>
            </div>
            <div class="auth-error" id="passwordError"></div>
        </div>

        <button type="submit" class="auth-submit">Confirm</button>
    </form>

</div>
@endsection
