<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Welcome, {{ auth()->user()->name }}</h2>
            <span class="badge-role {{ auth()->user()->role }}">{{ auth()->user()->role }}</span>
        </div>
    </x-slot>

    @php
        $role = auth()->user()->role;
        $hubs = [
            'admin' => [
                ['label' => 'Manager Mode', 'route' => 'manager.dashboard', 'icon' => '&#127918;', 'desc' => 'Run the club sim — staff, lanes, finance, confrontations.'],
            ],
            'steward' => [
                ['label' => 'Steward Desk', 'route' => 'steward.dashboard', 'icon' => '&#128722;', 'desc' => 'Manage the schedule, visitors, bans and complaints.'],
                ['label' => 'My Schedule', 'route' => 'steward.schedule.index', 'icon' => '&#128197;', 'desc' => 'View and complete your shifts.'],
            ],
            'caretaker' => [
                ['label' => 'Caretaker Desk', 'route' => 'caretaker.dashboard', 'icon' => '&#127919;', 'desc' => 'Inspect lanes, watch stock, keep the club running.'],
                ['label' => 'My Shifts', 'route' => 'caretaker.shifts.index', 'icon' => '&#9201;', 'desc' => 'Complete your shift and earn morale.'],
                ['label' => 'Crew Relations', 'route' => 'caretaker.crew.index', 'icon' => '&#128101;', 'desc' => 'See who you trust, who you avoid, and who is listening.'],
                ['label' => 'Inventory', 'route' => 'caretaker.inventory.index', 'icon' => '&#128230;', 'desc' => 'Track stock and log usage.'],
            ],
            'customer' => [
                ['label' => 'Book a Lane', 'route' => 'visitor.bookings.create', 'icon' => '&#127922;', 'desc' => 'Reserve a lane for your next visit.'],
                ['label' => 'My Bookings', 'route' => 'visitor.bookings.index', 'icon' => '&#128203;', 'desc' => 'View, cancel or review past games.'],
                ['label' => 'Waiting Queue', 'route' => 'visitor.queues.index', 'icon' => '&#9203;', 'desc' => 'Track your spot when lanes are full.'],
                ['label' => 'Reviews', 'route' => 'visitor.reviews.index', 'icon' => '&#11088;', 'desc' => 'Rate the club and read the latest word on the lanes.'],
            ],
        ];
        $cards = $hubs[$role] ?? [];
    @endphp

    <div style="padding:1.25rem;">
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
            @forelse ($cards as $card)
                <a href="{{ route($card['route']) }}" style="text-decoration:none;">
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1.1rem;transition:transform 0.15s, background 0.15s;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span style="font-size:1.5rem;">{!! $card['icon'] !!}</span>
                            <div>
                                <div style="font-family:var(--font-sub);font-size:0.85rem;color:var(--navy);font-weight:700;">{{ $card['label'] }}</div>
                                <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);margin-top:3px;">{{ $card['desc'] }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1.25rem;">
                    <div style="font-family:var(--font-mono);font-size:0.7rem;color:var(--slate);">No hub modules for your role yet. Head to <a href="{{ route('profile.edit') }}" style="color:var(--sky-dark);">your profile</a>.</div>
                </div>
            @endforelse
        </div>
    </div>
    <x-toast />

    @include('sim.partials.responsive')
</x-app-layout>
