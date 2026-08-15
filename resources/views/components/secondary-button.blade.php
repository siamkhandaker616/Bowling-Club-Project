@props(['type' => 'button'])

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'btn btn-ghost']) }} style="display:inline-flex;align-items:center;justify-content:center;padding:10px 28px;">
    {{ $slot }}
</button>
