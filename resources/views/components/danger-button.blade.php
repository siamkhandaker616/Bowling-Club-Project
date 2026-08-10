@props(['type' => 'submit'])

<button type="{{ $type }}" {{ $attributes->merge(['class' => '']) }} style="display:inline-flex;align-items:center;justify-content:center;padding:10px 28px;border:3px solid var(--coral);border-radius:50px;font-family:var(--font-header);font-size:0.75rem;text-transform:uppercase;letter-spacing:1px;cursor:pointer;background:var(--coral);color:var(--pin-white);transition:transform 0.15s,box-shadow 0.15s,background 0.15s;" onmouseover="this.style.background='var(--coral-dark)';this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(232,108,108,0.3)'" onmouseout="this.style.background='var(--coral)';this.style.transform='';this.style.boxShadow=''">
    {{ $slot }}
</button>
