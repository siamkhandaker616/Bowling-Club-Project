<section class="card" style="max-width:640px;">
    <header style="margin-bottom:1.2rem;">
        <h3>{{ __('Update Password') }}</h3>
        <p style="font-family:var(--font-body);font-size:0.85rem;color:var(--slate);margin-top:0.25rem;">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" novalidate class="gutter-form" style="margin-top:1.25rem;display:flex;flex-direction:column;gap:1rem;">
        @csrf
        @method('put')

        <div class="gutter-field field">
            <label class="label" for="update_password_current_password">Current Password <span class="req">*</span></label>
            <div class="inp-wrap">
                <input id="update_password_current_password" name="current_password" type="password" class="input{{ $errors->updatePassword->has('current_password') ? ' bad' : '' }}" autocomplete="current-password">
                <span class="gutter-flag">&#10003;</span>
            </div>
            <div class="gutter-err">@error('updatePassword.current_password'){{ $message }}@else Enter your current password @enderror</div>
        </div>

        <div class="gutter-field field">
            <label class="label" for="update_password_password">New Password <span class="req">*</span></label>
            <div class="inp-wrap">
                <input id="update_password_password" name="password" type="password" class="input{{ $errors->updatePassword->has('password') ? ' bad' : '' }}" autocomplete="new-password">
                <span class="gutter-flag">&#100003;</span>
            </div>
            <div class="gutter-err">@error('updatePassword.password'){{ $message }}@else Choose a strong password @enderror</div>
        </div>

        <div class="gutter-field field">
            <label class="label" for="update_password_password_confirmation">Confirm Password <span class="req">*</span></label>
            <div class="inp-wrap">
                <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="input{{ $errors->updatePassword->has('password_confirmation') ? ' bad' : '' }}" autocomplete="new-password">
                <span class="gutter-flag">&#10003;</span>
            </div>
            <div class="gutter-err">@error('updatePassword.password_confirmation'){{ $message }}@else Must match new password @enderror</div>
        </div>

        <div class="lane-stage">
            <div class="pin-rack">
                <div class="pin-row"><span class="pin"></span><span class="pin"></span><span class="pin"></span><span class="pin"></span></div>
                <div class="pin-row"><span class="pin"></span><span class="pin"></span><span class="pin"></span></div>
                <div class="pin-row"><span class="pin"></span><span class="pin"></span></div>
                <div class="pin-row"><span class="pin"></span></div>
            </div>
            <span class="ball-dot"></span>
        </div>

        <div style="display:flex;align-items:center;gap:1rem;">
            <button type="submit" class="btn-lane primary" style="font-size:0.65rem;padding:7px 18px;">Save</button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    style="font-family:var(--font-body);font-size:0.85rem;color:var(--sky-dark);"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
