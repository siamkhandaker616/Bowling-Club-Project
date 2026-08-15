@props(['type' => 'submit'])

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'btn']) }} style="display:inline-flex;align-items:center;justify-content:center;padding:10px 28px;background:var(--gold);color:var(--navy);">
    {{ $slot }}
</button>
