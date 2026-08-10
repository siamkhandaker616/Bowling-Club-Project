@props(['type' => 'submit'])

<button type="{{ $type }}" {{ $attributes->merge(['class' => '']) }} style="display:inline-flex;align-items:center;justify-content:center;padding:10px 28px;border:3px solid var(--navy);border-radius:50px;font-family:var(--font-header);font-size:0.75rem;text-transform:uppercase;letter-spacing:1px;cursor:pointer;background:var(--navy);color:var(--pin-white);transition:transform 0.15s,box-shadow 0.15s,background 0.15s;" onmouseover="this.style.background='var(--sky-dark)';this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(26,42,58,0.15)'" onmouseout="this.style.background='var(--navy)';this.style.transform='';this.style.boxShadow=''">
    {{ $slot }}
</button>
