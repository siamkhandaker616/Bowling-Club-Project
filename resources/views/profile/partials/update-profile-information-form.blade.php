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

    <form method="post" action="{{ route('profile.update') }}" novalidate style="margin-top:1.25rem;">
        @csrf
        @method('patch')

        <div style="margin-bottom:1rem;">
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div style="margin-bottom:1rem;">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

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

        <div style="display:flex;align-items:center;gap:1rem;">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

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
