@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => '']) }} style="padding:12px 16px;border:2px solid var(--sky-dark);border-radius:8px;background:var(--sky-light);font-family:var(--font-sub);font-size:0.85rem;color:var(--sky-dark);text-align:center;">
        {{ $status }}
    </div>
@endif
