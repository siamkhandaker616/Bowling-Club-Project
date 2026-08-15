@extends('layouts.guest')
@section('title', 'Member Sign In')

@section('content')
<section class="section" style="min-height:100vh;display:flex;flex-direction:column;justify-content:center;">
    <div class="wrap">
        <h2 class="section-title"><span class="pin-dot"></span>Member Sign In<span class="pin-dot"></span></h2>

        @if (session('status'))
            <div style="max-width:760px;margin:0 auto 1.2rem;padding:10px 14px;border:2px solid var(--ok);border-radius:8px;background:var(--sky-light);font-family:var(--font-sub);font-size:0.82rem;color:var(--ok);text-align:center">
                {{ session('status') }}
            </div>
        @endif

        <div class="access-panel">
            <div class="access-grid">
                <div class="access-form">
                    <h3 style="font-family:var(--font-header);font-size:.9rem;text-transform:uppercase;letter-spacing:1px;margin:0 0 1.2rem">Swipe in to the club</h3>

                    <form method="POST" action="{{ route('login') }}" novalidate id="loginForm">
                        @csrf
                        <div class="field">
                            <label class="label" for="email">Email <span class="req">*</span></label>
                            <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@example.com">
                            <div class="auth-error" id="emailError"></div>
                        </div>
                        <div class="field">
                            <label class="label" for="password">Password <span class="req">*</span></label>
                            <div class="pw-wrap">
                                <input class="input" id="password" type="password" name="password" required autocomplete="current-password" placeholder="Your access PIN" style="padding-right:64px">
                                <button type="button" class="pw-eye" onclick="togglePwText('password', this)" aria-label="Toggle password visibility">SHOW</button>
                            </div>
                            <div class="auth-error" id="passwordError"></div>
                        </div>
                        <div class="row-between">
                            <label class="check" style="margin:.2rem 0"><input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}><span class="box"></span>Keep me signed in</label>
                            <a href="#" onclick="openForgotModal(event)" style="font-size:.72rem">Forgot password?</a>
                        </div>
                        <p class="form-err {{ $errors->any() ? 'show' : '' }}" id="l-err">
                            @if ($errors->any()) {{ $errors->first() }} @else WRONG EMAIL OR PIN — THE GATE STAYS CLOSED. @endif
                        </p>
                        <button type="submit" class="submit" style="margin-top:1rem">Sign In &rarr;</button>
                    </form>

                    <div class="auth-or"><span></span><em>or</em><span></span></div>

                    <a href="{{ route('google.redirect') }}" class="google-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                        Continue with Google
                    </a>

                    <p style="font-family:var(--font-mono);font-size:.62rem;color:var(--slate);text-align:center;margin-top:1rem">Not a member yet? <a href="{{ route('register') }}">Create Account</a></p>
                </div>
                <div class="access-side">
                    <div class="lamp-row"><span class="lamp on"></span>Gate status &middot; OPEN</div>
                    <div class="lamp-row"><span class="lamp"></span>Front desk &middot; 2 staff on</div>
                    <div class="lamp-row"><span class="lamp"></span>Lane servers &middot; ONLINE</div>
                    <div class="lamp-row"><span class="lamp"></span>Waiting queue &middot; 6 parties</div>
                    <hr style="height:6px;border:none;background:repeating-linear-gradient(90deg,transparent 0 3px,rgba(42,31,22,.15) 3px 5px),linear-gradient(180deg,var(--lane-wood-light),var(--lane-wood-dark));border:2px solid var(--navy);margin:.4rem 0">
                    <div class="memo-curtain-wrap">
                        <div class="memo-curtain" id="demoCurtain" onclick="peelCurtain()"></div>
                        <p class="memo-note">DEMO ACCOUNTS — PIN: password<br>&bull; siamkhandaker616@gmail.com (manager)<br>&bull; sadmarre@gmail.com (steward)<br>&bull; naturallyskyblue@gmail.com (customer)<br>&bull; siam.khandaker@g.bracu.ac.bd (caretaker)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal-back" id="forgotModal">
    <div class="modal">
        <div class="modal-top">Reset your access PIN <button type="button" onclick="closeForgotModal()">&times;</button></div>
        <div class="modal-body">
            <p style="font-family:var(--font-body);font-size:.82rem;color:var(--slate);line-height:1.55;margin:0 0 1rem">
                Forgot your PIN? Enter your email and we'll send you a reset link.
            </p>
            <form method="POST" action="{{ route('password.email') }}" novalidate id="forgotForm">
                @csrf
                <div class="field">
                    <label class="label" for="femail">Email <span class="req">*</span></label>
                    <input class="input" id="femail" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="you@example.com">
                    <div class="auth-error" id="femailError"></div>
                </div>
                <button type="submit" class="submit" style="margin-top:.4rem">Send Reset Link &rarr;</button>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    function showForgot(e) {
        if (e) e.preventDefault();
        var m = document.getElementById('forgotModal');
        if (m) m.classList.add('open');
    }
    function hideForgot() {
        var m = document.getElementById('forgotModal');
        if (m) m.classList.remove('open');
    }
    document.getElementById('forgotModal').addEventListener('click', function (e) {
        if (e.target === this) hideForgot();
    });
    window.openForgotModal = showForgot;
    window.closeForgotModal = hideForgot;
})();

(function () {
    var clicks = 48;
    var curtain = document.getElementById('demoCurtain');
    if (!curtain) return;
    function peel() {
        clicks--;
        if (clicks <= 0) {
            curtain.classList.add('lifted');
            curtain.removeEventListener('click', peel);
        }
    }
    window.peelCurtain = peel;
})();
</script>
@endsection
