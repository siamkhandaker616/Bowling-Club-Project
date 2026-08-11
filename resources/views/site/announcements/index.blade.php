<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Announcements - The Tenth Frame</title>
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
            <a href="{{ url('/') }}" style="font-family:var(--font-sub);color:var(--navy);text-decoration:none;">Home</a>
            @auth
                <a href="{{ url('/dashboard') }}" class="btn" style="padding:8px 24px;font-size:0.85rem;">Dashboard</a>
            @else
                <a href="{{ route('login') }}" style="font-family:var(--font-sub);color:var(--navy);text-decoration:none;">Sign In</a>
            @endauth
        </nav>
    </header>

    <main style="max-width:1100px;margin:0 auto;padding:6rem 2rem 4rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;">
            <h1 style="font-family:var(--font-header);font-size:1.8rem;text-transform:uppercase;">Manage Announcements</h1>
            <a href="{{ route($rp.'create') }}" class="btn btn-gold" style="padding:10px 24px;font-size:0.8rem;">+ New Announcement</a>
        </div>

        <div style="background:var(--pin-white);border:var(--border);border-radius:12px;overflow:hidden;box-shadow:var(--shadow-md);">
            <div style="background:var(--navy);padding:0.75rem 1.5rem;display:grid;grid-template-columns:60px 1fr 100px 80px 120px;gap:1rem;font-family:var(--font-mono);font-size:0.7rem;color:var(--sky-light);text-transform:uppercase;letter-spacing:1px;">
                <span>ID</span>
                <span>Title</span>
                <span>Priority</span>
                <span>Active</span>
                <span>Actions</span>
            </div>

            @forelse($announcements as $a)
                <div style="padding:0.75rem 1.5rem;border-bottom:1px solid var(--fog);display:grid;grid-template-columns:60px 1fr 100px 80px 120px;gap:1rem;align-items:center;font-size:0.85rem;transition:background 0.15s;" onmouseover="this.style.background='var(--mist)'" onmouseout="this.style.background=''">
                    <span style="font-family:var(--font-mono);color:var(--slate);">{{ $a->id }}</span>
                    <div>
                        <span style="font-family:var(--font-sub);">{{ $a->title }}</span>
                        <p style="font-size:0.75rem;color:var(--slate);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:400px;">{{ $a->body }}</p>
                    </div>
                    <span style="font-family:var(--font-sub);font-size:0.75rem;padding:3px 10px;border-radius:50px;text-align:center;{{ $a->priority === 'urgent' ? 'background:var(--coral-light);color:var(--coral-dark);' : 'background:var(--sky-light);color:var(--navy);' }}">
                        {{ $a->priority }}
                    </span>
                    <span style="font-family:var(--font-mono);font-size:0.75rem;color:{{ $a->is_active ? 'var(--navy)' : 'var(--slate)' }};">
                        {{ $a->is_active ? 'ON' : 'OFF' }}
                    </span>
                    <div style="display:flex;gap:0.5rem;">
                        <a href="{{ route($rp.'edit', $a) }}" style="padding:5px 12px;border:2px solid var(--navy);border-radius:6px;font-family:var(--font-sub);font-size:0.7rem;text-decoration:none;color:var(--navy);background:var(--sky-light);transition:background 0.15s;" onmouseover="this.style.background='var(--sky)'" onmouseout="this.style.background='var(--sky-light)'">Edit</a>
                        <form method="POST" action="{{ route($rp.'destroy', $a) }}" onsubmit="return confirm('Delete this announcement?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="padding:5px 12px;border:2px solid var(--coral);border-radius:6px;font-family:var(--font-sub);font-size:0.7rem;color:var(--coral);background:var(--coral-light);cursor:pointer;transition:background 0.15s;" onmouseover="this.style.background='var(--coral)'; this.style.color='var(--pin-white)'" onmouseout="this.style.background='var(--coral-light)'; this.style.color='var(--coral)'">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div style="padding:3rem;text-align:center;color:var(--slate);font-family:var(--font-sub);">
                    <div style="font-size:2rem;margin-bottom:0.5rem;">🎳</div>
                    The board's empty. No announcements on the lane yet — hit "+ New Announcement" to get the first one rolling.
                </div>
            @endforelse
        </div>
    </main>

    <x-toast />
</body>
</html>
