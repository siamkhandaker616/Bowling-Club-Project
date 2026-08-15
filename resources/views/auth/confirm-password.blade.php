@extends('layouts.guest')
@section('title', 'Confirm Password')

@section('content')
<section class="section" style="min-height:100vh;display:flex;flex-direction:column;justify-content:center;">
    <div class="wrap">
        <h2 class="section-title"><span class="pin-dot"></span>Confirm Password<span class="pin-dot"></span></h2>

        <div class="access-panel" style="max-width:440px">
            <div class="access-form">
                <p style="font-family:var(--font-body);font-size:0.85rem;color:var(--slate);text-align:center;line-height:1.55;margin:0 0 1.2rem">
                    This is a secure area. Please confirm your password before continuing.
                </p>

                <form method="POST" action="{{ route('password.confirm') }}" novalidate id="confirmForm">
                    @csrf
                    <div class="field">
                        <label class="label" for="password">Password <span class="req">*</span></label>
                        <div class="pw-wrap">
                            <input class="input" id="password" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password" style="padding-right:64px">
                            <button type="button" class="pw-eye" onclick="togglePwText('password', this)" aria-label="Toggle password visibility">SHOW</button>
                        </div>
                        <div class="auth-error" id="passwordError"></div>
                    </div>
                    <button type="submit" class="submit" style="margin-top:.6rem">Confirm &rarr;</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
