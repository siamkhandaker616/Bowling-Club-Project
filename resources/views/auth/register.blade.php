@extends('layouts.guest')
@section('title', 'Create Account')

@section('content')
<section class="section" style="min-height:100vh;display:flex;flex-direction:column;justify-content:center;">
    <div class="wrap">
        <h2 class="section-title"><span class="pin-dot"></span>Create Account<span class="pin-dot"></span></h2>

        <div class="access-panel">
            <div class="access-grid">
                <div class="access-form">
                    <h3 style="font-family:var(--font-header);font-size:.9rem;text-transform:uppercase;letter-spacing:1px;margin:0 0 1.2rem">Join The Tenth Frame</h3>

                    <form method="POST" action="{{ route('register') }}" novalidate id="registerForm">
                        @csrf
                        <div class="field">
                            <label class="label" for="name">Name <span class="req">*</span></label>
                            <input class="input" id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Your full name">
                            <div class="auth-error" id="nameError"></div>
                        </div>
                        <div class="field">
                            <label class="label" for="email">Email <span class="req">*</span></label>
                            <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="you@example.com">
                            <div class="auth-error" id="emailError"></div>
                        </div>
                        <div class="field">
                            <label class="label" for="password">Password <span class="req">*</span></label>
                            <div class="pw-wrap">
                                <input class="input" id="password" type="password" name="password" required autocomplete="new-password" placeholder="Create a password" style="padding-right:64px">
                                <button type="button" class="pw-eye" onclick="togglePwText('password', this)" aria-label="Toggle password visibility">SHOW</button>
                            </div>
                            <div class="reg-meter" id="pwStrength" data-strength="0"><span id="pw1"></span><span id="pw2"></span><span id="pw3"></span><span id="pw4"></span></div>
                            <div class="reg-label" id="pwLabel">Enter a password</div>
                            <div class="auth-error" id="passwordError"></div>
                        </div>
                        <div class="field">
                            <label class="label" for="password_confirmation">Confirm Password <span class="req">*</span></label>
                            <div class="pw-wrap">
                                <input class="input" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repeat your password" style="padding-right:64px">
                                <button type="button" class="pw-eye" onclick="togglePwText('password_confirmation', this)" aria-label="Toggle password visibility">SHOW</button>
                            </div>
                            <div class="auth-error" id="password_confirmationError"></div>
                        </div>
                        <label class="check"><input type="checkbox"><span class="box"></span>I can bowl under pressure</label>
                        <label class="check"><input type="checkbox" checked><span class="box"></span>Send me league night alerts</label>
                        <p class="form-err {{ $errors->any() ? 'show' : '' }}" id="r-err">
                            @if ($errors->any()) {{ $errors->first() }} @else CHECK YOUR DETAILS — THE DESK REJECTED THAT ENTRY. @endif
                        </p>
                        <button type="submit" class="submit" style="margin-top:1rem">Create Account &rarr;</button>
                    </form>

                    <div class="auth-or"><span></span><em>or</em><span></span></div>

                    <a href="{{ route('google.redirect') }}" class="google-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                        Sign up with Google
                    </a>

                    <p style="font-family:var(--font-mono);font-size:.62rem;color:var(--slate);text-align:center;margin-top:1rem">Already a member? <a href="{{ route('login') }}">Sign In</a></p>
                </div>
                <div class="access-side">
                    <div class="lamp-row"><span class="lamp on"></span>Membership &middot; 2,104 bowlers</div>
                    <div class="lamp-row"><span class="lamp"></span>Lane reservations at the desk</div>
                    <div class="lamp-row"><span class="lamp"></span>Queue position on your phone</div>
                    <div class="lamp-row"><span class="lamp"></span>Season stats &amp; fixture alerts</div>
                    <hr style="height:6px;border:none;background:repeating-linear-gradient(90deg,transparent 0 3px,rgba(42,31,22,.15) 3px 5px),linear-gradient(180deg,var(--lane-wood-light),var(--lane-wood-dark));border:2px solid var(--navy);margin:.4rem 0">
                    <p class="memo-note">A fresh account gets a Visitor pass on the sim desk. Pin it to the lane board — it opens every feature here.</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
