@php($noMarginTop = $noMarginTop ?? false)
<footer style="background:var(--navy);color:var(--fog);padding:3rem 2rem;text-align:center;{{ $noMarginTop ? '' : 'margin-top:4rem;' }}">
    <div class="ball-accent" style="width:28px;height:28px;margin:0 auto 1rem;"></div>
    <p style="font-family:var(--font-display);font-size:1.2rem;color:var(--pin-white);margin-bottom:0.5rem;">The Tenth Frame</p>
    <p style="font-family:var(--font-sub);font-size:0.85rem;color:var(--fog);">The Tenth Frame Bowling Club &copy; {{ date('Y') }} &bull; Strike fast, roll loud.</p>
</footer>
