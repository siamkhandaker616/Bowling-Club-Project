@props(['value'])

<a {{ $attributes->merge(['class' => '']) }} style="display:block;width:100%;padding:8px 16px;font-family:var(--font-sub);font-size:0.85rem;color:var(--navy);text-decoration:none;transition:background 0.15s,color 0.15s;" onmouseover="this.style.background='var(--sky-light)';this.style.color='var(--sky-dark)'" onmouseout="this.style.background='transparent';this.style.color='var(--navy)'">
    {{ $value ?? $slot }}
</a>
