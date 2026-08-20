@php
    $activeRoute = $activeRoute ?? null;

    $bagCount = 0;
    $isAuth = Auth::check();
    $role = $isAuth ? Auth::user()->role : null;

    if ($isAuth && $role === 'customer') {
        $bagCount = (int) \App\Models\CartItem::where('session_id', session()->getId())->sum('quantity');
    }

    $dashRoute = match($role) {
        'admin'    => 'manager.dashboard',
        'steward'  => 'steward.dashboard',
        'caretaker'=> 'caretaker.dashboard',
        default    => 'visitor.dashboard',
    };

    $isDashboard = $isAuth && request()->routeIs($dashRoute);
    $badgeClass = $role === 'admin' ? 'manager' : $role;
    $badgeLabel = $role === 'admin' ? 'Manager' : ucfirst($role);

    $on = fn ($route) => $activeRoute === $route;
@endphp
<header style="position:fixed;top:0;left:0;right:0;z-index:100;background:rgba(247,241,227,.95);border-bottom:3px solid var(--navy);padding:.8rem 2rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;">
    <a class="brand" href="{{ route('home') }}">
        <span class="brand-ball"></span>
        <span class="brand-name">The Tenth Frame</span>
    </a>

    <div class="nav-right">
        @if($isAuth)
            @if($isDashboard)
                <a href="{{ route('home') }}" class="btn btn-ghost btn-nav">Home</a>
            @else
                <a href="{{ route($dashRoute) }}" class="btn btn-ghost btn-nav">Dashboard</a>
            @endif
        @endif

        @if(Route::has('site.facility-map'))
            <a href="{{ route('site.facility-map') }}" class="btn btn-ghost btn-nav {{ $on('site.facility-map') ? 'btn-coral' : '' }}">Facility Map</a>
        @endif

        @if(Route::has('public.fixtures'))
            <a href="{{ route('public.fixtures') }}" class="btn btn-ghost btn-nav {{ $on('public.fixtures') ? 'btn-coral' : '' }}">Fixtures</a>
        @endif

        @if($isAuth && $role === 'customer' && Route::has('public.proshop.cart'))
            <a href="{{ route('public.proshop.cart') }}" class="btn btn-ghost btn-nav {{ $on('public.proshop.cart') ? 'btn-coral' : '' }}" style="position:relative;">
                Bag
                @if($bagCount > 0)
                    <span style="position:absolute;top:-8px;right:-8px;min-width:20px;height:20px;border-radius:50%;background:var(--rubber);color:var(--pin-white);font-family:var(--font-mono);font-size:0.65rem;display:flex;align-items:center;justify-content:center;padding:0 5px;font-weight:700;">{{ $bagCount }}</span>
                @endif
            </a>
        @endif

        @if($isAuth && $role === 'admin' && Route::has('site.announcements.index'))
            <a href="{{ route('site.announcements.index') }}" class="btn btn-ghost btn-nav {{ $on('site.announcements.index') ? 'btn-coral' : '' }}">Announcement</a>
        @endif

        @if(!$isAuth)
            @if(Route::has('login'))
                <a href="{{ route('login') }}" class="btn btn-ghost btn-nav">Sign In</a>
            @endif
            @if(Route::has('register'))
                <a href="{{ route('register') }}" class="btn btn-ghost btn-nav">Join the Club</a>
            @endif
        @endif

        @if($isAuth)
            <div class="user-pop" x-data="{ userOpen: false }">
                <button @click="userOpen = !userOpen" class="user-btn" title="{{ Auth::user()->name }}">
                    <span class="portrait mood-happy">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
                    <span class="user-name">{{ Auth::user()->name }}</span>
                    <svg width="12" height="12" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
                <div x-show="userOpen" @click.away="userOpen = false" x-cloak class="user-menu">
                    <a href="{{ route('profile.edit') }}">Profile</a>
                    <form method="POST" action="{{ route('logout') }}" style="margin:0">
                        @csrf
                        <button type="submit">Log Out</button>
                    </form>
                </div>
            </div>

            <span class="badge-role {{ $badgeClass }}">{{ $badgeLabel }}</span>
        @endif
    </div>
</header>
