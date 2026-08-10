@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-input']) }} style="width:100%;padding:10px 14px;border:2px solid var(--fog);border-radius:10px;font-family:var(--font-body);font-size:0.95rem;background:var(--pin-white);color:var(--navy);transition:border-color 0.15s;outline:none;" onfocus="this.style.borderColor='var(--sky-dark)'" onblur="this.style.borderColor='var(--fog)'">
