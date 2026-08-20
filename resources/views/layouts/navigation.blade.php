<header class="header" x-data="{ menuOpen: false }">
    <a class="brand" href="{{ route('home') }}">
        <span class="brand-ball"></span>
        <span class="brand-name">The Tenth Frame</span>
    </a>

    @php
        $role = Auth::user()->role;
        $navBagCount = $role === 'customer' ? (int) \App\Models\CartItem::where('session_id', session()->getId())->sum('quantity') : 0;

        $dashRoute = match($role) {
            'admin' => 'manager.dashboard',
            'steward' => 'steward.dashboard',
            'caretaker' => 'caretaker.dashboard',
            default => 'visitor.dashboard',
        };
        $isDashboard = request()->routeIs($dashRoute);
        $badgeClass = $role === 'admin' ? 'manager' : $role;
        $badgeLabel = $role === 'admin' ? 'Manager' : ucfirst($role);
    @endphp

    <div class="nav-right">
        @if($isDashboard)
            <a href="{{ route('home') }}" class="btn btn-ghost btn-nav">Home</a>
        @else
            <a href="{{ route($dashRoute) }}" class="btn btn-ghost btn-nav">Dashboard</a>
        @endif

        <a href="{{ route('site.facility-map') }}" class="btn btn-ghost btn-nav">Facility Map</a>

        @if(Route::has('public.fixtures'))
            <a href="{{ route('public.fixtures') }}" class="btn btn-ghost btn-nav">Fixtures</a>
        @endif

        @if($role === 'customer')
            <a href="{{ route('public.proshop.cart') }}" class="btn btn-ghost btn-nav" title="Your Bag" style="position:relative;">
                Bag
                @if($navBagCount > 0)
                    <span style="position:absolute;top:-8px;right:-8px;min-width:20px;height:20px;border-radius:50%;background:var(--rubber);color:var(--pin-white);font-family:var(--font-mono);font-size:0.65rem;display:flex;align-items:center;justify-content:center;padding:0 5px;font-weight:700;">{{ $navBagCount }}</span>
                @endif
            </a>
        @endif

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

        @if($role === 'admin')
            @include('sim.partials.settings-dropdown')
        @endif

        <button class="hamburger" @click="menuOpen = !menuOpen" aria-label="Toggle menu"><span></span><span></span><span></span></button>
    </div>

    <nav class="nav show" x-show="menuOpen" @click.away="menuOpen = false" x-cloak style="display:none">
        @if($role === 'admin')
            <a class="nav-link {{ request()->routeIs('manager.*') ? 'on' : '' }}" href="{{ route('manager.dashboard') }}">Dashboard</a>
        @elseif($role === 'steward')
            <a class="nav-link {{ request()->routeIs('steward.*') ? 'on' : '' }}" href="{{ route('steward.dashboard') }}">Dashboard</a>
        @elseif($role === 'caretaker')
            <a class="nav-link {{ request()->routeIs('caretaker.*') ? 'on' : '' }}" href="{{ route('caretaker.dashboard') }}">Dashboard</a>
        @else
            <a class="nav-link {{ request()->routeIs('visitor.*') ? 'on' : '' }}" href="{{ route('visitor.dashboard') }}">Dashboard</a>
        @endif
        <a class="nav-link" href="{{ route('profile.edit') }}">Profile</a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="nav-link" style="padding:0">Sign Out</button>
        </form>
    </nav>
</header>
