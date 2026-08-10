<section>
    <header>
        <h2 style="font-family:var(--font-sub);font-size:1.1rem;color:var(--coral);">
            {{ __('Delete Account') }}
        </h2>
        <p style="font-family:var(--font-body);font-size:0.85rem;color:var(--slate);margin-top:0.25rem;">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <div style="margin-top:1.25rem;">
        <x-danger-button
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        >{{ __('Delete Account') }}</x-danger-button>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" style="padding:1.5rem;">
            @csrf
            @method('delete')

            <h2 style="font-family:var(--font-sub);font-size:1.1rem;color:var(--navy);">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p style="font-family:var(--font-body);font-size:0.85rem;color:var(--slate);margin-top:0.5rem;line-height:1.5;">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div style="margin-top:1.25rem;">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />
                <x-text-input id="password" name="password" type="password" placeholder="{{ __('Password') }}" />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:1.5rem;">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>
                <x-danger-button>
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
