@props(['active'])

@php
$isActive = $active ?? false;
@endphp

<a {{ $attributes->merge(['class' => '']) }} style="display:inline-flex;align-items:center;padding:4px 12px;border-bottom:2px solid {{ $isActive ? 'var(--gold)' : 'transparent' }};font-family:var(--font-sub);font-size:0.85rem;color:{{ $isActive ? 'var(--gold)' : 'var(--fog)' }};text-decoration:none;transition:color 0.15s,border-color 0.15s;" onmouseover="if('{{ $isActive }}' !== '1') this.style.color='var(--pin-white)';this.style.borderColor='var(--fog)'" onmouseout="this.style.color='{{ $isActive ? 'var(--gold)' : 'var(--fog)' }}';this.style.borderColor='{{ $isActive ? 'var(--gold)' : 'transparent' }}'">
    {{ $slot }}
</a>
