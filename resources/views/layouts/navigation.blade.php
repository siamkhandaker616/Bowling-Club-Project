<nav x-data="{ menuOpen: false }" style="background: var(--navy); border-bottom: 3px solid var(--gold); position: sticky; top: 0; z-index: 50;">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 2rem;">
        <div style="display: flex; justify-content: space-between; height: 64px; align-items: center;">
            <div style="display: flex; align-items: center; gap: 2rem;">
                <!-- Logo -->
                <a href="{{ route('dashboard') }}" style="text-decoration: none; display: flex; align-items: center; gap: 8px;">
                    <div class="ball-accent" style="width: 28px; height: 28px;"></div>
                    <span style="font-family: var(--font-display); font-size: 1.1rem; color: var(--pin-white); text-transform: uppercase;">The Tenth Frame</span>
                </a>

                <!-- Nav Links -->
                <div style="display: flex; gap: 1.5rem;" class="hidden sm:flex">
                    @php $role = Auth::user()->role; @endphp

                    @if($role === 'admin')
                        <a href="{{ route('manager.dashboard') }}" style="font-family: var(--font-sub); font-size: 0.9rem; text-decoration: none; color: {{ request()->routeIs('manager.*') ? 'var(--gold)' : 'var(--fog)' }}; transition: color 0.15s;" onmouseover="this.style.color='var(--gold-light)'" onmouseout="this.style.color={{ request()->routeIs('manager.*') ? 'var(--gold)' : 'var(--fog)' }}">
                            Dashboard
                        </a>
                        <a href="#" style="font-family: var(--font-sub); font-size: 0.9rem; text-decoration: none; color: var(--fog); transition: color 0.15s;" onmouseover="this.style.color='var(--gold-light)'" onmouseout="this.style.color='var(--fog)'">
                            Staff
                        </a>
                        <a href="#" style="font-family: var(--font-sub); font-size: 0.9rem; text-decoration: none; color: var(--fog); transition: color 0.15s;" onmouseover="this.style.color='var(--gold-light)'" onmouseout="this.style.color='var(--fog)'">
                            Lanes
                        </a>
                        <a href="#" style="font-family: var(--font-sub); font-size: 0.9rem; text-decoration: none; color: var(--fog); transition: color 0.15s;" onmouseover="this.style.color='var(--gold-light)'" onmouseout="this.style.color='var(--fog)'">
                            Config
                        </a>

                    @elseif($role === 'steward')
                        <a href="{{ route('steward.dashboard') }}" style="font-family: var(--font-sub); font-size: 0.9rem; text-decoration: none; color: {{ request()->routeIs('steward.*') ? 'var(--gold)' : 'var(--fog)' }}; transition: color 0.15s;" onmouseover="this.style.color='var(--gold-light)'" onmouseout="this.style.color={{ request()->routeIs('steward.*') ? 'var(--gold)' : 'var(--fog)' }}">
                            Dashboard
                        </a>
                        <a href="#" style="font-family: var(--font-sub); font-size: 0.9rem; text-decoration: none; color: var(--fog); transition: color 0.15s;" onmouseover="this.style.color='var(--gold-light)'" onmouseout="this.style.color='var(--fog)'">
                            Events
                        </a>
                        <a href="#" style="font-family: var(--font-sub); font-size: 0.9rem; text-decoration: none; color: var(--fog); transition: color 0.15s;" onmouseover="this.style.color='var(--gold-light)'" onmouseout="this.style.color='var(--fog)'">
                            Visitors
                        </a>
                        <a href="#" style="font-family: var(--font-sub); font-size: 0.9rem; text-decoration: none; color: var(--fog); transition: color 0.15s;" onmouseover="this.style.color='var(--gold-light)'" onmouseout="this.style.color='var(--fog)'">
                            Bar
                        </a>

                    @elseif($role === 'caretaker')
                        <a href="{{ route('caretaker.dashboard') }}" style="font-family: var(--font-sub); font-size: 0.9rem; text-decoration: none; color: {{ request()->routeIs('caretaker.*') ? 'var(--gold)' : 'var(--fog)' }}; transition: color 0.15s;" onmouseover="this.style.color='var(--gold-light)'" onmouseout="this.style.color={{ request()->routeIs('caretaker.*') ? 'var(--gold)' : 'var(--fog)' }}">
                            Dashboard
                        </a>
                        <a href="#" style="font-family: var(--font-sub); font-size: 0.9rem; text-decoration: none; color: var(--fog); transition: color 0.15s;" onmouseover="this.style.color='var(--gold-light)'" onmouseout="this.style.color='var(--fog)'">
                            Shifts
                        </a>
                        <a href="#" style="font-family: var(--font-sub); font-size: 0.9rem; text-decoration: none; color: var(--fog); transition: color 0.15s;" onmouseover="this.style.color='var(--gold-light)'" onmouseout="this.style.color='var(--fog)'">
                            Tasks
                        </a>

                    @else
                        <!-- customer -->
                        <a href="{{ route('visitor.dashboard') }}" style="font-family: var(--font-sub); font-size: 0.9rem; text-decoration: none; color: {{ request()->routeIs('visitor.*') ? 'var(--gold)' : 'var(--fog)' }}; transition: color 0.15s;" onmouseover="this.style.color='var(--gold-light)'" onmouseout="this.style.color={{ request()->routeIs('visitor.*') ? 'var(--gold)' : 'var(--fog)' }}">
                            Dashboard
                        </a>
                        <a href="#" style="font-family: var(--font-sub); font-size: 0.9rem; text-decoration: none; color: var(--fog); transition: color 0.15s;" onmouseover="this.style.color='var(--gold-light)'" onmouseout="this.style.color='var(--fog)'">
                            Fixtures
                        </a>
                        <a href="#" style="font-family: var(--font-sub); font-size: 0.9rem; text-decoration: none; color: var(--fog); transition: color 0.15s;" onmouseover="this.style.color='var(--gold-light)'" onmouseout="this.style.color='var(--fog)'">
                            Events
                        </a>
                        <a href="#" style="font-family: var(--font-sub); font-size: 0.9rem; text-decoration: none; color: var(--fog); transition: color 0.15s;" onmouseover="this.style.color='var(--gold-light)'" onmouseout="this.style.color='var(--fog)'">
                            Book
                        </a>
                    @endif
                </div>
            </div>

            <!-- User Dropdown -->
            <div style="display: flex; align-items: center; gap: 1rem;" class="hidden sm:flex">
                <span style="font-family: var(--font-mono); font-size: 0.8rem; color: var(--fog);">{{ ucfirst(Auth::user()->role) }}</span>
                <div x-data="{ userOpen: false }" style="position: relative;">
                    <button @click="userOpen = !userOpen" style="display: flex; align-items: center; gap: 6px; padding: 6px 14px; background: rgba(255,255,255,0.1); border: 2px solid var(--slate); border-radius: 50px; color: var(--pin-white); font-family: var(--font-sub); font-size: 0.85rem; cursor: pointer; transition: border-color 0.15s;" onmouseover="this.style.borderColor='var(--sky)'" onmouseout="this.style.borderColor='var(--slate)'">
                        {{ Auth::user()->name }}
                        <svg width="12" height="12" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>

                    <div x-show="userOpen" @click.away="userOpen = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" style="position: absolute; right: 0; top: 100%; margin-top: 4px; min-width: 180px; background: var(--pin-white); border: 2px solid var(--navy); border-radius: 12px; box-shadow: var(--shadow-md); z-index: 50; overflow: hidden;">
                        <a href="{{ route('profile.edit') }}" style="display: block; padding: 10px 16px; font-family: var(--font-sub); font-size: 0.85rem; color: var(--navy); text-decoration: none; border-bottom: 1px solid var(--fog);" onmouseover="this.style.background='var(--mist)'" onmouseout="this.style.background=''">Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" style="display: block; width: 100%; padding: 10px 16px; font-family: var(--font-sub); font-size: 0.85rem; color: var(--coral); text-decoration: none; background: none; border: none; cursor: pointer; text-align: left;" onmouseover="this.style.background='var(--mist)'" onmouseout="this.style.background=''">
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Hamburger -->
            <button @click="menuOpen = !menuOpen" style="display: none; padding: 8px; background: none; border: none; color: var(--fog); cursor: pointer;" class="sm:hidden">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="menuOpen" @click.away="menuOpen = false" class="sm:hidden" style="border-top: 2px solid var(--slate); padding: 1rem 2rem;">
        @if($role === 'admin')
            <a href="{{ route('manager.dashboard') }}" style="display: block; padding: 0.75rem 0; font-family: var(--font-sub); color: var(--pin-white); text-decoration: none;">Dashboard</a>
        @elseif($role === 'steward')
            <a href="{{ route('steward.dashboard') }}" style="display: block; padding: 0.75rem 0; font-family: var(--font-sub); color: var(--pin-white); text-decoration: none;">Dashboard</a>
        @elseif($role === 'caretaker')
            <a href="{{ route('caretaker.dashboard') }}" style="display: block; padding: 0.75rem 0; font-family: var(--font-sub); color: var(--pin-white); text-decoration: none;">Dashboard</a>
        @else
            <a href="{{ route('visitor.dashboard') }}" style="display: block; padding: 0.75rem 0; font-family: var(--font-sub); color: var(--pin-white); text-decoration: none;">Dashboard</a>
        @endif
        <a href="{{ route('profile.edit') }}" style="display: block; padding: 0.75rem 0; font-family: var(--font-sub); color: var(--fog); text-decoration: none;">Profile</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" style="padding: 0.75rem 0; font-family: var(--font-sub); color: var(--coral); text-decoration: none; background: none; border: none; cursor: pointer;">Log Out</button>
        </form>
    </div>
</nav>
