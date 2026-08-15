@extends('layouts.guest')
@section('title', 'Reset Password')

@section('content')
<section class="section" style="min-height:100vh;display:flex;flex-direction:column;justify-content:center;">
    <div class="wrap">
        <h2 class="section-title"><span class="pin-dot"></span>Set a new PIN<span class="pin-dot"></span></h2>

        <div class="access-panel" style="max-width:440px">
            <div class="access-form">
                <form method="POST" action="{{ route('password.store') }}" novalidate id="resetForm">
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div class="field">
                        <label class="label" for="email">Email <span class="req">*</span></label>
                        <input class="input" id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" placeholder="you@example.com">
                        <div class="auth-error" id="emailError"></div>
                    </div>
                    <div class="field">
                        <label class="label" for="password">New Password <span class="req">*</span></label>
                        <div class="pw-wrap">
                            <input class="input" id="password" type="password" name="password" required autocomplete="new-password" placeholder="Enter new password" style="padding-right:64px">
                            <button type="button" class="pw-eye" onclick="togglePwText('password', this)" aria-label="Toggle password visibility">SHOW</button>
                        </div>
                        <div class="auth-error" id="passwordError"></div>
                    </div>
                    <div class="field">
                        <label class="label" for="password_confirmation">Confirm Password <span class="req">*</span></label>
                        <div class="pw-wrap">
                            <input class="input" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repeat new password" style="padding-right:64px">
                            <button type="button" class="pw-eye" onclick="togglePwText('password_confirmation', this)" aria-label="Toggle password visibility">SHOW</button>
                        </div>
                        <div class="auth-error" id="password_confirmationError"></div>
                    </div>
                    <button type="submit" class="submit" style="margin-top:.6rem">Reset Password &rarr;</button>
                </form>

                <p style="font-family:var(--font-mono);font-size:.62rem;color:var(--slate);text-align:center;margin-top:1rem">Remember your password? <a href="{{ route('login') }}">Sign In</a></p>
            </div>
        </div>
    </div>
</section>
@endsection
