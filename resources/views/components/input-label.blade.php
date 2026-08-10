@props(['value'])

<label {{ $attributes->merge(['class' => 'block']) }} style="font-family:var(--font-sub);font-size:0.85rem;color:var(--slate);display:block;margin-bottom:4px;">
    {{ $value ?? $slot }}
</label>
