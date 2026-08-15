@props(['type' => 'submit'])

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'btn btn-coral']) }} style="display:inline-flex;align-items:center;justify-content:center;padding:10px 28px;">
    {{ $slot }}
</button>
