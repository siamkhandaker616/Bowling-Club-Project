<header style="position:fixed;top:0;left:0;right:0;z-index:50;background:rgba(245,248,250,0.95);backdrop-filter:blur(8px);border-bottom:3px solid var(--navy);padding:0.75rem 2rem;display:flex;align-items:center;justify-content:space-between;">
    <a href="/" style="text-decoration:none;display:flex;align-items:center;gap:10px;">
        <div class="ball-accent" style="width:32px;height:32px;"></div>
        <span style="font-family:var(--font-display);font-size:1.3rem;color:var(--navy);text-transform:uppercase;">The Tenth Frame</span>
    </a>
    <nav style="display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap;">
        {{ $slot }}
        @if (Route::has('login'))
            @auth
                <a href="{{ url('/dashboard') }}" class="btn" style="padding:8px 24px;font-size:0.85rem;">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-ghost" style="padding:8px 20px;font-size:0.8rem;">Sign In</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn" style="padding:8px 20px;font-size:0.8rem;">Join the Club</a>
                @endif
            @endauth
        @endif
    </nav>
</header>
