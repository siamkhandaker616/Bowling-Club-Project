@php
    $icons = [
        'speech' => '&#128172;',
        'thought' => '&#128173;',
        'exclamation' => '&#9888;',
        'question' => '&#10067;',
    ];
@endphp

@once
    <style>
        .dlg{display:flex;flex-direction:column;gap:6px;}
        .dlg-row{display:flex;align-items:flex-start;gap:6px;max-width:100%;}
        .dlg-bubble{font-family:var(--font-mono);font-size:0.58rem;line-height:1.45;color:var(--navy);border-radius:10px;padding:6px 9px;border:2px solid var(--navy);}
        .dlg-bubble.speech{background:var(--pin-white);}
        .dlg-bubble.thought{background:var(--sky-light);border-style:dashed;}
        .dlg-bubble.exclamation{background:var(--coral-light);border-color:var(--coral-dark);color:var(--coral-dark);}
        .dlg-bubble.question{background:var(--gold-light);}
        .dlg-ic{font-size:0.7rem;margin-top:3px;}
    </style>
@endonce

<div class="dlg">
    @foreach ($bubbles as $bubble)
        <div class="dlg-row">
            <span class="dlg-ic">{!! $icons[$bubble['type']] ?? $icons['speech'] !!}</span>
            <div class="dlg-bubble {{ $bubble['type'] ?? 'speech' }}">{{ $bubble['text'] ?? '' }}</div>
        </div>
    @endforeach
</div>
