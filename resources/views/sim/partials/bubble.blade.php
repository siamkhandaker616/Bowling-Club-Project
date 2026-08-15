@php
    $type = $type ?? 'tip';
    $accent = match ($type) {
        'good' => 'var(--sky-dark)',
        'bad' => 'var(--coral)',
        'warn' => 'var(--gold-dust)',
        default => 'var(--navy)',
    };
    $bg = match ($type) {
        'good' => 'var(--sky-light)',
        'bad' => 'var(--coral-light)',
        'warn' => 'var(--gold-light)',
        default => 'var(--mist)',
    };
    $glyph = match ($type) {
        'good' => '&#10003;',
        'bad' => '&#9888;',
        'warn' => '&#8252;',
        default => '&#128172;',
    };
@endphp
<div class="sim-bubble sim-bubble-{{ $type }}" data-bubble-type="{{ $type }}" style="position:relative;background:{{ $bg }};border:2px solid {{ $accent }};border-radius:10px;padding:10px 14px;margin-bottom:8px;font-family:var(--font-body);">
    <span style="position:absolute;top:-8px;right:14px;width:0;height:0;border-left:8px solid transparent;border-right:8px solid transparent;border-bottom:8px solid {{ $accent }};"></span>
    <div style="display:flex;align-items:center;gap:8px;">
        <span style="font-family:var(--font-mono);font-size:1rem;color:{{ $accent }};">{!! $glyph !!}</span>
        <div>
            <div style="font-family:var(--font-sub);font-size:0.72rem;color:{{ $accent }};">{{ $title }}</div>
            @isset($message)
                <div style="font-family:var(--font-body);font-size:0.7rem;color:var(--navy);">{{ $message }}</div>
            @endisset
        </div>
    </div>
    @isset($slot)
        <div style="margin-top:6px;">{{ $slot }}</div>
    @endisset
</div>
