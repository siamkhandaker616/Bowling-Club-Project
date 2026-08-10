@props(['active'])

@php
$isActive = $active ?? false;
@endphp

<a {{ $attributes->merge(['class' => '']) }} style="display:block;width:100%;padding:8px 16px;border-left:3px solid {{ $isActive ? 'var(--gold)' : 'transparent' }};font-family:var(--font-sub);font-size:0.85rem;color:{{ $isActive ? 'var(--navy)' : 'var(--slate)' }};text-decoration:none;background:{{ $isActive ? 'var(--sky-light)' : 'transparent' }};transition:all 0.15s;" onmouseover="this.style.background='var(--sky-light)';this.style.color='var(--navy)'" onmouseout="this.style.background='{{ $isActive ? 'var(--sky-light)' : 'transparent' }}';this.style.color='{{ $isActive ? 'var(--navy)' : 'var(--slate)' }}'">
    {{ $slot }}
</a>
