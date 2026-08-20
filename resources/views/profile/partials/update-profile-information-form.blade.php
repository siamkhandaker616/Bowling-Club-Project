<section class="card" style="max-width:640px;">
    <header style="margin-bottom:1.2rem;">
        <h3>{{ __('Profile Information') }}</h3>
        <p style="font-family:var(--font-body);font-size:0.85rem;color:var(--slate);margin-top:0.25rem;">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" novalidate class="gutter-form" style="margin-top:1.25rem;display:flex;flex-direction:column;gap:1rem;">
        @csrf
        @method('patch')

        <div class="gutter-field field">
            <label class="label" for="name">Name <span class="req">*</span></label>
            <div class="inp-wrap">
                <input id="name" name="name" type="text" class="input{{ $errors->has('name') ? ' bad' : '' }}" value="{{ old('name', $user->name)}}" required autofocus autocomplete="name">
                <span class="gutter-flag">&#10003;</span>
            </div>
            <div class="gutter-err">@error('name'){{ $message }}@else Name is required @enderror</div>
        </div>

        <div class="gutter-field field">
            <label class="label" for="email">Email <span class="req">*</span></label>
            <div class="inp-wrap">
                <input id="email" name="email" type="email" class="input{{ $errors->has('email') ? ' bad' : '' }}" value="{{ old('email', $user->email)}}" required autocomplete="username">
                <span class="gutter-flag">&#10003;</span>
            </div>
            <div class="gutter-err">@error('email'){{ $message }}@else Email is required @enderror</div>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div style="margin-top:0.5rem;">
                    <p style="font-family:var(--font-body);font-size:0.85rem;color:var(--slate);">
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification" style="font-family:var(--font-body);font-size:0.85rem;color:var(--sky-dark);background:none;border:none;cursor:pointer;text-decoration:underline;padding:0;" onmouseover="this.style.color='var(--navy)'" onmouseout="this.style.color='var(--sky-dark)'">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p style="font-family:var(--font-body);font-size:0.85rem;font-weight:600;color:var(--sky-dark);margin-top:0.5rem;">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
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

            @if (session('status') === 'profile-updated')
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
