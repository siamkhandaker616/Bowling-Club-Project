@php
    $m = auth()->user();
    $initials = strtoupper(substr($m->name,0,1)) . strtoupper(substr(str_replace(' ','',$m->name),-1));
    $links = [
        ['route' => 'manager.dashboard',    'label' => 'Overview',      'icon' => '&#127918;'],
        ['route' => 'manager.staff.index',   'label' => 'Staff',         'icon' => '&#128101;'],
        ['route' => 'manager.inventory.index','label' => 'Inventory',    'icon' => '&#128230;'],
        ['route' => 'manager.inventory.purchases.index','label' => 'Purchase Bills','icon' => '&#128176;'],
        ['route' => 'manager.league.index',  'label' => 'League',       'icon' => '&#127944;'],
        ['route' => 'manager.bookings.index','label' => 'Bookings',      'icon' => '&#127903;'],
        ['route' => 'site.announcements.index','label' => 'Announcements','icon' => '&#128227;'],
        ['route' => 'manager.complaints.index','label' => 'Complaints',  'icon' => '&#9878;'],
        ['route' => 'manager.confrontations.index','label' => 'Confrontations','icon' => '&#9881;'],
        ['route' => 'manager.bans.index',    'label' => 'Bans',          'icon' => '&#128683;'],
        ['route' => 'manager.reviews.index', 'label' => 'Reviews',       'icon' => '&#11088;'],
        ['route' => 'manager.touring.index', 'label' => 'Touring',       'icon' => '&#128742;'],
    ];
    $active = fn($route) => request()->routeIs(str_replace('.index','.*',$route));
@endphp
<div class="mod-rail">
    <div class="rail-head"><span class="rail-led"></span>Manager Panel<span class="rail-led"></span></div>
    @foreach ($links as $link)
        <a href="{{ route($link['route']) }}" class="mod-link {{ $active($link['route']) ? 'on' : '' }}"><span class="ml-dot"></span>{!! $link['icon'] !!}<span style="flex:1;">{{ $link['label'] }}</span></a>
    @endforeach
    <div class="rail-avatar">
        <div class="portrait mood-happy" style="width:36px;height:36px;font-size:.65rem;border-color:var(--gold)">{{ $initials }}</div>
        <div>
            <span style="font-family:var(--font-sub);font-size:.68rem;color:var(--pin-white);display:block">{{ ucfirst($m->name) }}</span>
            <span style="font-family:var(--font-mono);font-size:.5rem;color:var(--fog);letter-spacing:1px">MANAGER</span>
        </div>
    </div>
</div>
