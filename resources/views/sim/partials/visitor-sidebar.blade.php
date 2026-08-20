@php
    $u = auth()->user();
    $initials = strtoupper(substr($u->name,0,1)) . strtoupper(substr(str_replace(' ','',$u->name),-1));
    $links = [
        ['route' => 'visitor.dashboard',        'label' => 'Overview',     'num' => '01'],
        ['route' => 'visitor.bookings.index',   'label' => 'My Bookings',  'num' => '02'],
        ['route' => 'game.leaderboard',         'label' => 'My Scores',    'num' => '03'],
        ['route' => 'site.snackbar',            'label' => 'Snack Bar',    'num' => '04'],
        ['route' => 'site.facility-map',        'label' => 'Facility Map', 'num' => '05'],
        ['route' => 'public.events',            'label' => 'Events',       'num' => '06'],
        ['route' => 'public.fixtures',          'label' => 'Fixtures',     'num' => '07'],
        ['route' => 'public.touring',           'label' => 'Touring',      'num' => '08'],
        ['route' => 'public.stats',             'label' => 'Stats',        'num' => '09'],
        ['route' => 'public.proshop.index',     'label' => 'Pro Shop',     'num' => '10'],
        ['route' => 'visitor.queues.index',     'label' => 'Queue',        'num' => '11'],
        ['route' => 'visitor.reviews.index',    'label' => 'Reviews',      'num' => '12'],
        ['route' => 'visitor.complaints.index', 'label' => 'Complaints',   'num' => '13'],
    ];
    $active = fn($route) => request()->routeIs($route);
@endphp
<div class="mod-rail rail-visitor">
    <div class="rail-head"><span class="rail-led"></span>My Account<span class="rail-led"></span></div>
    @foreach ($links as $link)
        <a href="{{ route($link['route']) }}" class="mod-link {{ $active($link['route']) ? 'on' : '' }}"><span class="ml-dot"></span><span class="ml-num">{{ $link['num'] }}</span>{{ $link['label'] }}</a>
    @endforeach
    <div class="rail-actions">
        <a href="{{ route('visitor.bookings.create') }}" class="btn btn-coral">Book a Lane &#8594;</a>
    </div>
    <div class="rail-avatar">
        <div class="portrait mood-happy" style="width:36px;height:36px;font-size:.65rem;border-color:var(--sky-dark)">{{ $initials }}</div>
        <div>
            <span style="font-family:var(--font-sub);font-size:.68rem;color:var(--pin-white);display:block">{{ $u->name }}</span>
            <span style="font-family:var(--font-mono);font-size:.5rem;color:var(--fog);letter-spacing:1px">CLIENT &middot; MEMBER</span>
        </div>
    </div>
</div>
