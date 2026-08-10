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
                Please fix the errors below.
            </div>
        @endif

        <form method="POST" action="{{ route($rp.'update', $announcement) }}" style="background:var(--pin-white);border:var(--border);border-radius:12px;padding:2rem;box-shadow:var(--shadow-md);">
            @csrf
            @method('PUT')
            <div style="margin-bottom:1.5rem;">
                <label style="display:block;font-family:var(--font-sub);font-size:0.85rem;color:var(--slate);margin-bottom:6px;">Title</label>
                <input type="text" name="title" value="{{ old('title', $announcement->title) }}" required
                    style="width:100%;padding:10px 14px;border:2px solid var(--fog);border-radius:8px;font-family:var(--font-body);font-size:0.9rem;background:var(--cloud);color:var(--navy);outline:none;transition:border-color 0.2s;box-sizing:border-box;"
                    onfocus="this.style.borderColor='var(--navy)'" onblur="this.style.borderColor='var(--fog)'">
                @error('title') <span style="font-size:0.75rem;color:var(--coral);margin-top:4px;display:block;">{{ $message }}</span> @enderror
            </div>

            <div style="margin-bottom:1.5rem;">
                <label style="display:block;font-family:var(--font-sub);font-size:0.85rem;color:var(--slate);margin-bottom:6px;">Body</label>
                <textarea name="body" rows="5" required
                    style="width:100%;padding:10px 14px;border:2px solid var(--fog);border-radius:8px;font-family:var(--font-body);font-size:0.9rem;background:var(--cloud);color:var(--navy);outline:none;transition:border-color 0.2s;box-sizing:border-box;resize:vertical;"
                    onfocus="this.style.borderColor='var(--navy)'" onblur="this.style.borderColor='var(--fog)'">{{ old('body', $announcement->body) }}</textarea>
                @error('body') <span style="font-size:0.75rem;color:var(--coral);margin-top:4px;display:block;">{{ $message }}</span> @enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">
                <div>
                    <label style="display:block;font-family:var(--font-sub);font-size:0.85rem;color:var(--slate);margin-bottom:6px;">Priority</label>
                    <select name="priority" required
                        style="width:100%;padding:10px 14px;border:2px solid var(--fog);border-radius:8px;font-family:var(--font-body);font-size:0.9rem;background:var(--cloud);color:var(--navy);outline:none;box-sizing:border-box;">
                        <option value="normal" {{ old('priority', $announcement->priority) === 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="urgent" {{ old('priority', $announcement->priority) === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-family:var(--font-sub);font-size:0.85rem;color:var(--slate);margin-bottom:6px;">Published At</label>
                    <input type="datetime-local" name="published_at" value="{{ old('published_at', $announcement->published_at ? $announcement->published_at->format('Y-m-d\TH:i') : '') }}"
                        style="width:100%;padding:10px 14px;border:2px solid var(--fog);border-radius:8px;font-family:var(--font-body);font-size:0.9rem;background:var(--cloud);color:var(--navy);outline:none;box-sizing:border-box;">
                </div>
            </div>

            <div style="margin-bottom:2rem;">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-family:var(--font-sub);font-size:0.85rem;color:var(--slate);">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $announcement->is_active) ? 'checked' : '' }}
                        style="width:18px;height:18px;accent-color:var(--navy);">
                    Active (visible on ticker)
                </label>
            </div>

            <div style="display:flex;gap:1rem;">
                <button type="submit" class="btn btn-gold" style="padding:12px 32px;">Update Announcement</button>
                <a href="{{ route($rp.'index') }}" style="display:inline-block;padding:12px 32px;border:2px solid var(--navy);border-radius:50px;font-family:var(--font-header);font-size:0.85rem;text-transform:uppercase;letter-spacing:1px;color:var(--navy);background:var(--pin-white);text-decoration:none;transition:background 0.15s;" onmouseover="this.style.background='var(--mist)'" onmouseout="this.style.background='var(--pin-white)'">Cancel</a>
            </div>
        </form>
    </main>
</body>
</html>
