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

    $border = match ($type) {
        'success' => '#4caf7d',
        'error' => 'var(--coral)',
        default => 'var(--fog)',
    };
@endphp

@if ($message)
    <div id="x-toast" role="status" style="position:fixed;top:5rem;left:50%;transform:translateX(-50%);z-index:9999;padding:12px 24px;border-radius:8px;font-family:var(--font-sub);font-size:0.85rem;color:var(--pin-white);background:var(--navy);border:2px solid {{ $border }};box-shadow:var(--shadow-md);opacity:0;transition:opacity 0.3s;">
        {{ $message }}
    </div>
    <script>
    (function() {
        var t = document.getElementById('x-toast');
        t.style.opacity = '1';
        setTimeout(function() {
            t.style.opacity = '0';
            setTimeout(function() { t.remove(); }, 300);
        }, 3000);
    })();
    </script>
@endif
