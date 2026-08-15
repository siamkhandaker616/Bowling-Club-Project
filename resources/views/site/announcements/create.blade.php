<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Announcement - The Tenth Frame</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body style="min-height:100vh;">
    @php $rp = 'site.announcements.'; @endphp
    <header style="position:fixed;top:0;left:0;right:0;z-index:50;background:rgba(245,248,250,0.95);backdrop-filter:blur(8px);border-bottom:3px solid var(--navy);padding:0.75rem 2rem;display:flex;align-items:center;justify-content:space-between;">
        <a href="/" style="text-decoration:none;display:flex;align-items:center;gap:10px;">
            <div class="ball-accent" style="width:32px;height:32px;"></div>
            <span style="font-family:var(--font-display);font-size:1.3rem;color:var(--navy);text-transform:uppercase;">The Tenth Frame</span>
        </a>
        <nav style="display:flex;align-items:center;gap:1.5rem;">
            <a href="/" style="font-family:var(--font-sub);color:var(--navy);text-decoration:none;">Home</a>
            <a href="{{ route($rp.'index') }}" style="font-family:var(--font-sub);color:var(--navy);text-decoration:none;">All Announcements</a>
        </nav>
    </header>

    <main style="max-width:700px;margin:0 auto;padding:6rem 2rem 4rem;">
        <h1 style="font-family:var(--font-header);font-size:1.8rem;text-transform:uppercase;margin-bottom:2rem;">New Announcement</h1>

        @if($errors->any())
            <div style="background:#fde3e3;border:2px solid var(--coral);border-radius:8px;padding:1rem 1.5rem;margin-bottom:1.5rem;font-family:var(--font-sub);font-size:0.85rem;color:var(--coral-dark);">
                &#9888; Gutter ball — something needs fixing before this announcement can roll. Check the fields below.
            </div>
        @endif

        <form method="POST" action="{{ route($rp.'store') }}" style="background:var(--pin-white);border:var(--border);border-radius:12px;padding:2rem;box-shadow:var(--shadow-md);">
            @csrf
            <div style="margin-bottom:1.5rem;">
                <label style="display:block;font-family:var(--font-sub);font-size:0.85rem;color:var(--slate);margin-bottom:6px;">Title</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                    style="width:100%;padding:10px 14px;border:2px solid var(--fog);border-radius:8px;font-family:var(--font-body);font-size:0.9rem;background:var(--cloud);color:var(--navy);outline:none;transition:border-color 0.2s;box-sizing:border-box;"
                    onfocus="this.style.borderColor='var(--navy)'" onblur="this.style.borderColor='var(--fog)'">
                @error('title') <span style="font-size:0.75rem;color:var(--coral);margin-top:4px;display:block;">{{ $message }}</span> @enderror
            </div>

            <div style="margin-bottom:1.5rem;">
                <label style="display:block;font-family:var(--font-sub);font-size:0.85rem;color:var(--slate);margin-bottom:6px;">Body</label>
                <textarea name="body" rows="5" required
                    style="width:100%;padding:10px 14px;border:2px solid var(--fog);border-radius:8px;font-family:var(--font-body);font-size:0.9rem;background:var(--cloud);color:var(--navy);outline:none;transition:border-color 0.2s;box-sizing:border-box;resize:vertical;"
                    onfocus="this.style.borderColor='var(--navy)'" onblur="this.style.borderColor='var(--fog)'">{{ old('body') }}</textarea>
                @error('body') <span style="font-size:0.75rem;color:var(--coral);margin-top:4px;display:block;">{{ $message }}</span> @enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">
                <div>
                    <label style="display:block;font-family:var(--font-sub);font-size:0.85rem;color:var(--slate);margin-bottom:6px;">Priority</label>
                    <select name="priority" required class="fold-select"
                        style="width:100%;padding:10px 14px;border:2px solid var(--fog);border-radius:8px;font-family:var(--font-body);font-size:0.9rem;background:var(--cloud);color:var(--navy);outline:none;box-sizing:border-box;">
                        <option value="normal" {{ old('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>
                <div style="grid-column:1 / -1;">
                    <label style="display:block;font-family:var(--font-sub);font-size:0.85rem;color:var(--slate);margin-bottom:6px;">Published At</label>
                    @php
                        $pubVal = old('published_at', date('Y-m-d\TH:i'));
                        $pubDate = \Carbon\Carbon::parse($pubVal);
                    @endphp
                    <div class="lc" id="published-at-lc" data-year="{{ $pubDate->year }}">
                        <div class="lc-head">
                            <button type="button" class="lc-nav" aria-label="Previous month">&laquo;</button>
                            <div class="lc-mo"></div>
                            <button type="button" class="lc-nav" aria-label="Next month">&raquo;</button>
                        </div>
                        <div class="lc-frame"><div class="lc-grid"></div></div>
                        <div class="lc-read"><span class="lc-key">Publish</span><span class="lc-picked" data-kept="1">{{ $pubDate->format('M j, Y g:i A') }}</span></div>
                        <input type="hidden" name="published_at" class="lc-input" value="{{ $pubVal }}">
                        <input type="hidden" class="lc-m" value="{{ $pubDate->month - 1 }}">
                    </div>
                </div>
            </div>

            <div style="margin-bottom:2rem;">
                <input type="hidden" name="is_active" value="0">
                <label class="pin-check" style="cursor:pointer;font-family:var(--font-sub);font-size:0.85rem;color:var(--slate);">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                    <span class="pin-box"></span> Active (visible on ticker)
                </label>
            </div>

            <div style="display:flex;gap:1rem;">
                <button type="submit" class="btn btn-gold" style="padding:12px 32px;">Create Announcement</button>
                <a href="{{ route($rp.'index') }}" style="display:inline-block;padding:12px 32px;border:2px solid var(--navy);border-radius:50px;font-family:var(--font-header);font-size:0.85rem;text-transform:uppercase;letter-spacing:1px;color:var(--navy);background:var(--pin-white);text-decoration:none;transition:background 0.15s;" onmouseover="this.style.background='var(--mist)'" onmouseout="this.style.background='var(--pin-white)'">Cancel</a>
            </div>
        </form>
    </main>
    <x-toast />

    @include('sim.partials.fold-controls')

    <script>
        (function () {
            var lc = document.getElementById('published-at-lc');
            if (!lc) return;
            var hid = lc.querySelector('.lc-input');
            var m = (hid.value || '').match(/T(\d{2}:\d{2})/);
            var timePart = m ? m[1] : '';
            var picked = lc.querySelector('.lc-picked');
            lc.addEventListener('click', function (e) {
                if (!e.target.classList.contains('lc-day')) return;
                var v = hid.value;
                if (v && timePart && v.indexOf('T') === -1) hid.value = v + 'T' + timePart;
                if (picked) {
                    var t = timePart ? timePart.split(':') : null;
                    var hr = t ? +t[0] % 12 : 12, ap = t ? (+t[0] >= 12 ? 'PM' : 'AM') : '';
                    var cur = picked.textContent;
                    if (t && cur.indexOf(',') === -1) picked.textContent = cur + ', ' + (hr === 0 ? 12 : hr) + ':' + t[1] + ' ' + ap;
                }
            });
        })();
    </script>
</body>
</html>
