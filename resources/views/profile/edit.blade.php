<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight" style="font-family:var(--font-header);color:var(--navy);text-transform:uppercase;letter-spacing:1px;">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div style="max-width:640px;margin:0 auto;">
        <div style="margin-bottom:1.5rem;">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div style="margin-bottom:1.5rem;">
            @include('profile.partials.update-password-form')
        </div>

        <div>
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>
