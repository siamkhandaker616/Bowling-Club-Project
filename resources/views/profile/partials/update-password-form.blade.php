<section>
    <header>
        <h2 style="font-family:var(--font-sub);font-size:1.1rem;color:var(--navy);">
            {{ __('Update Password') }}
        </h2>
        <p style="font-family:var(--font-body);font-size:0.85rem;color:var(--slate);margin-top:0.25rem;">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" style="margin-top:1.25rem;">
        @csrf
        @method('put')

        <div style="margin-bottom:1rem;">
            <x-input-label for="update_password_current_password" :value="__('Current Password')" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div style="margin-bottom:1rem;">
            <x-input-label for="update_password_password" :value="__('New Password')" />
            <x-text-input id="update_password_password" name="password" type="password" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div style="margin-bottom:1rem;">
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div style="display:flex;align-items:center;gap:1rem;">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

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
