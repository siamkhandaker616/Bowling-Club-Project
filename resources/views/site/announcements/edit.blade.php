<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Announcement - The Tenth Frame</title>
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
        <h1 style="font-family:var(--font-header);font-size:1.8rem;text-transform:uppercase;margin-bottom:2rem;">Edit Announcement</h1>

        @if($errors->any())
            <div style="background:#fde3e3;border:2px solid var(--coral);border-radius:8px;padding:1rem 1.5rem;margin-bottom:1.5rem;font-family:var(--font-sub);font-size:0.85rem;color:var(--coral-dark);">
                &#9888; Gutter ball — something needs fixing before this edit sticks. Check the fields below.
            </div>
        @endif

        <form method="POST" action="{{ route($rp.'update', $announcement) }}" novalidate class="gutter-form" style="background:var(--pin-white);border:var(--border);border-radius:12px;padding:2rem;box-shadow:var(--shadow-md);">
            @csrf
            @method('PUT')
            <div class="gutter-field" style="margin-bottom:1.5rem;">
                <label class="label">Title <span class="req">*</span></label>
                <div class="inp-wrap">
                    <input class="input" type="text" name="title" value="{{ old('title', $announcement->title) }}" required>
                    <span class="gutter-flag">&#10003;</span>
                </div>
                <div class="gutter-err">Title is required</div>
            </div>

            <div class="gutter-field" style="margin-bottom:1.5rem;">
                <label class="label">Body <span class="req">*</span></label>
                <div class="inp-wrap">
                    <textarea class="input" name="body" rows="5" required>{{ old('body', $announcement->body) }}</textarea>
                    <span class="gutter-flag">&#10003;</span>
                </div>
                <div class="gutter-err">Body is required</div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">
                <div class="gutter-field">
                    <label class="label">Priority</label>
                    <div class="inp-wrap">
                        <select name="priority" required class="fold-select input">
                            <option value="normal" {{ old('priority', $announcement->priority) === 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="urgent" {{ old('priority', $announcement->priority) === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>
                </div>
                <div style="grid-column:1 / -1;" class="gutter-field">
                    <label class="label">Published At</label>
                    @php
                        $pubVal = old('published_at', $announcement->published_at ? $announcement->published_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i'));
                        try { $pubDate = \Carbon\Carbon::parse($pubVal); } catch (\Throwable) { $pubDate = \Carbon\Carbon::now(); }
                    @endphp
                    <div class="inp-wrap">
                        <input class="input" type="datetime-local" name="published_at" data-datepicker value="{{ $pubVal }}">
                    </div>
                </div>
            </div>

            <div style="margin-bottom:2rem;">
                <input type="hidden" name="is_active" value="0">
                <label class="pin-check" style="cursor:pointer;font-family:var(--font-sub);font-size:0.85rem;color:var(--slate);">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $announcement->is_active) ? 'checked' : '' }}>
                    <span class="pin-box"></span> Show on the home page ticker
                </label>
            </div>

            <div class="lane-stage">
                <div class="pin-rack">
                    <div class="pin-row"><span class="pin"></span><span class="pin"></span><span class="pin"></span><span class="pin"></span><span class="pin"></span></div>
                    <div class="pin-row"><span class="pin"></span><span class="pin"></span><span class="pin"></span><span class="pin"></span></div>
                    <div class="pin-row"><span class="pin"></span><span class="pin"></span><span class="pin"></span></div>
                    <div class="pin-row"><span class="pin"></span><span class="pin"></span></div>
                    <div class="pin-row"><span class="pin"></span></div>
                </div>
                <span class="ball-dot"></span>
            </div>

            <div style="display:flex;gap:1rem;">
                <button type="submit" class="submit">Update Announcement &rarr;</button>
                <a href="{{ route($rp.'index') }}" style="display:inline-block;padding:12px 32px;border:2px solid var(--navy);border-radius:50px;font-family:var(--font-header);font-size:0.85rem;text-transform:uppercase;letter-spacing:1px;color:var(--navy);background:var(--pin-white);text-decoration:none;transition:background 0.15s;" onmouseover="this.style.background='var(--mist)'" onmouseout="this.style.background='var(--pin-white)'">Cancel</a>
            </div>
        </form>
    </main>
    <x-toast />

    @include('sim.partials.fold-controls')

    <script src="/js/datepicker.js"></script>
</body>
</html>
