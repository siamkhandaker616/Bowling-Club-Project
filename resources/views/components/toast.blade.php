@php
    $type = 'neutral';
    $message = null;

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
        <span class="t-ball"></span>
        <span>{{ $message }}</span>
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
        }, 3000);
    })();
    </script>
@endif
