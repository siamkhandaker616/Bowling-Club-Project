@php
    $u = auth()->user();
    $role = $u->role;
    $active = request()->route() ? request()->route()->getName() : '';
    $initials = strtoupper(substr($u->name, 0, 1)) . strtoupper(substr(str_replace(' ', '', $u->name), -1));
    $on = fn ($base) => $active === $base || str_starts_with($active, $base . '.');

    $roleCss = ['customer' => 'visitor', 'admin' => 'admin'];

    $dock = [
        'admin' => [
            'head' => 'Manager Panel',
            'mood' => 'mood-happy',
            'border' => 'var(--gold)',
            'links' => [
                ['num' => '01', 'label' => 'Overview',       'route' => 'manager.dashboard'],
                ['num' => '02', 'label' => 'Staff',          'route' => 'manager.staff.index'],
                ['num' => '03', 'label' => 'Inventory',      'route' => 'manager.inventory.index'],
                ['num' => '04', 'label' => 'Purchase Bills', 'route' => 'manager.inventory.purchases.index'],
                ['num' => '05', 'label' => 'League',        'route' => 'manager.league.index'],
                ['num' => '06', 'label' => 'Bookings',       'route' => 'manager.bookings.index'],
                ['num' => '07', 'label' => 'Announcements',  'route' => 'site.announcements.index'],
                ['num' => '08', 'label' => 'Complaints',     'route' => 'manager.complaints.index'],
                ['num' => '09', 'label' => 'Confrontations', 'route' => 'manager.confrontations.index'],
                ['num' => '10', 'label' => 'Bans',           'route' => 'manager.bans.index'],
                ['num' => '11', 'label' => 'Reviews',        'route' => 'manager.reviews.index'],
                ['num' => '12', 'label' => 'Touring',        'route' => 'manager.touring.index'],
            ],
            'actions' => null,
        ],
        'steward' => [
            'head' => 'Steward Panel',
            'mood' => 'mood-happy',
            'border' => 'var(--coral)',
            'links' => [
                ['num' => '01', 'label' => 'Overview',      'route' => 'steward.dashboard'],
                ['num' => '02', 'label' => 'Schedule',      'route' => 'steward.schedule.index'],
                ['num' => '03', 'label' => 'Visitors',      'route' => 'steward.visitors.index'],
                ['num' => '04', 'label' => 'Facility Map',  'route' => 'site.facility-map'],
                ['num' => '05', 'label' => 'Complaints',    'route' => 'steward.complaints.index'],
                ['num' => '06', 'label' => 'Bans',          'route' => 'steward.bans.index'],
                ['num' => '07', 'label' => 'Snitch Inbox',  'route' => 'steward.snitch.index'],
            ],
            'actions' => null,
        ],
        'caretaker' => [
            'head' => 'Caretaker Panel',
            'mood' => 'mood-ok',
            'border' => 'var(--lane-wood)',
            'links' => [
                ['num' => '01', 'label' => 'Overview',      'route' => 'caretaker.dashboard'],
                ['num' => '02', 'label' => 'Shifts',        'route' => 'caretaker.shifts.index'],
                ['num' => '03', 'label' => 'Facility Map',  'route' => 'site.facility-map'],
                ['num' => '04', 'label' => 'Crew',          'route' => 'caretaker.crew.index'],
                ['num' => '05', 'label' => 'Inventory',     'route' => 'caretaker.inventory.index'],
                ['num' => '06', 'label' => 'Match Prep',    'route' => 'caretaker.prep.index'],
            ],
            'actions' => null,
        ],
        'customer' => [
            'head' => 'My Account',
            'mood' => 'mood-happy',
            'border' => 'var(--sky-dark)',
            'links' => [
                ['num' => '01', 'label' => 'Overview',      'route' => 'visitor.dashboard'],
                ['num' => '02', 'label' => 'Book a Lane',   'route' => 'visitor.bookings.create'],
                ['num' => '03', 'label' => 'My Bookings',   'route' => 'visitor.bookings.index'],
                ['num' => '04', 'label' => 'My Scores',     'route' => 'game.leaderboard'],
                ['num' => '05', 'label' => 'Facility Map',  'route' => 'site.facility-map'],
                ['num' => '06', 'label' => 'Pro Shop',      'route' => 'public.proshop.index'],
                ['num' => '07', 'label' => 'Queue',         'route' => 'visitor.queues.index'],
                ['num' => '08', 'label' => 'Reviews',       'route' => 'visitor.reviews.index'],
                ['num' => '09', 'label' => 'Complaints',    'route' => 'visitor.complaints.index'],
            ],
            'actions' => ['route' => 'visitor.bookings.create', 'label' => 'Book a Lane &#8594;'],
        ],
    ][$role] ?? null;

    $cfg = \App\Models\ClubConfig::singleton();
    $day = $role === 'admin' ? $cfg->current_day : null;
    $roleLabels = [
        'admin' => 'MANAGER &middot; REPUTATION ' . $cfg->reputation,
        'steward' => 'STEWARD',
        'caretaker' => 'CARETAKER &middot; LANE CREW',
        'customer' => 'CLIENT &middot; MEMBER',
    ];
    $roleLabel = $roleLabels[$role] ?? strtoupper($role);
@endphp
<div class="mod-rail rail-{{ $roleCss[$role] ?? $role }}" data-rail data-role="{{ $role }}" data-active="{{ $active }}">
    <div class="rail-head"><span class="rail-led"></span>{{ $dock['head'] }}<span class="rail-led"></span></div>

    @if ($day !== null)
    <div class="rail-day">
        <span class="rd-label">Day</span>
        <span class="rd-num">{{ $day }}</span>
        <form method="POST" action="{{ route('manager.day.advance') }}" style="margin:0;display:flex;margin-left:auto;">
            @csrf
            <button type="submit" class="rd-btn">Next Day &rarr;</button>
        </form>
        <form method="POST" action="{{ route('manager.day.toggleBadDay') }}" style="margin:0 0 0 .35rem;display:flex;">
            @csrf
            <button type="submit" class="rd-btn rd-bad {{ $cfg->bad_day_mode ? 'bad-on' : '' }}">Bad Day</button>
        </form>
    </div>
    @endif

    @foreach ($dock['links'] as $link)
            <a href="{{ route($link['route']) }}" class="mod-link {{ $on($link['route']) ? 'on' : '' }}">
                <span class="ml-dot"></span><span class="ml-num">{{ $link['num'] }}</span>{{ $link['label'] }}
            </a>
    @endforeach

    @if (! empty($dock['actions']))
    <div class="rail-actions">
        <a href="{{ route($dock['actions']['route']) }}" class="btn btn-coral">{!! $dock['actions']['label'] !!}</a>
    </div>
    @endif

    <div class="rail-avatar">
        <div class="portrait {{ $dock['mood'] }}" style="width:36px;height:36px;font-size:.65rem;border-color:{{ $dock['border'] }}">{{ $initials }}</div>
        <div>
            <span style="font-family:var(--font-sub);font-size:.68rem;color:var(--pin-white);display:block">{{ $u->name }}</span>
            <span style="font-family:var(--font-mono);font-size:.5rem;color:var(--fog);letter-spacing:1px">{!! $roleLabel !!}</span>
        </div>
    </div>
</div>
