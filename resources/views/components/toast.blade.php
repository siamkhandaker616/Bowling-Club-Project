@php
    $type = 'neutral';
    $message = null;
    $toastImage = session('toast_image');

    if (session('success')) {
        $type = 'success';
        $message = session('success');
    } elseif (session('error')) {
        $type = 'error';
        $message = session('error');
    } elseif (session('flash')) {
        $flash = session('flash');
        if (is_array($flash)) {
            $type = in_array($flash['type'] ?? '', ['success', 'error'], true) ? $flash['type'] : 'neutral';
            $message = $flash['message'] ?? '';
        } else {
            $message = $flash;
        }
    }
@endphp

@if ($message)
    <div id="x-toast" role="status" class="toast {{ $type === 'error' ? 'err' : '' }}" style="position:fixed;bottom:1.4rem;right:1.4rem;z-index:9999;">
        @if ($toastImage)
            <img src="{{ $toastImage }}" alt="" style="position:absolute;top:-18px;left:-14px;width:64px;height:64px;object-fit:cover;border:3px solid var(--pin-white);box-shadow:2px 3px 8px rgba(0,0,0,.35);border-radius:2px;transform:rotate(-8deg);z-index:1;pointer-events:none;">
        @endif
        <span class="t-ball"></span>
        <span style="white-space:pre-line;">{{ $message }}</span>
    </div>
    <script>
    (function() {
        var t = document.getElementById('x-toast');
        requestAnimationFrame(function() {
            t.classList.add('show');
        });
        setTimeout(function() {
            t.classList.remove('show');
            setTimeout(function() { t.remove(); }, 400);
        }, 4000);
    })();
    </script>
@endif
