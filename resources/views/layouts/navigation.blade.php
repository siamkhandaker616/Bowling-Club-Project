<header class="header" x-data="{ menuOpen: false }">
    <a class="brand" href="{{ route('dashboard') }}">
        <span class="brand-ball"></span>
        <span class="brand-name">The Tenth Frame</span>
    </a>

    @php
        $role = Auth::user()->role;
        $navBagCount = $role === 'customer' ? (int) \App\Models\CartItem::where('session_id', session()->getId())->sum('quantity') : 0;
    @endphp

    <div class="nav-right">
        @php
            $dashRoute = match($role) {
                'admin' => 'manager.dashboard',
                'steward' => 'steward.dashboard',
                'caretaker' => 'caretaker.dashboard',
                default => 'visitor.dashboard',
            };
            $badgeClass = $role === 'admin' ? 'manager' : $role;
            $badgeLabel = $role === 'admin' ? 'Manager' : ucfirst($role);
        @endphp

        <a href="{{ route($dashRoute) }}" class="btn btn-ghost btn-nav" title="Home">Home</a>
        <a href="{{ route('site.facility-map') }}" class="btn btn-ghost btn-nav" title="Facility Map">Facility Map</a>

        @if($role === 'customer')
            <a href="{{ route('public.proshop.cart') }}" class="btn btn-ghost btn-nav" title="Your Bag" style="position:relative;">
                Bag
                @if($navBagCount > 0)
                    <span style="position:absolute;top:-6px;right:-10px;min-width:18px;height:18px;border-radius:50%;background:var(--coral);color:var(--pin-white);font-family:var(--font-mono);font-size:.6rem;display:flex;align-items:center;justify-content:center;padding:0 4px;font-weight:700;border:2px solid var(--navy);">{{ $navBagCount }}</span>
                @endif
            </a>
        @endif

        <span class="badge-role {{ $badgeClass }}">{{ $badgeLabel }}</span>

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
