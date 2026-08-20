@php
    $u = auth()->user();
    $initials = strtoupper(substr($u->name,0,1)) . strtoupper(substr(str_replace(' ','',$u->name),-1));
    $links = [
        ['route' => 'caretaker.dashboard',      'label' => 'Overview',  'num' => '01'],
        ['route' => 'caretaker.shifts.index',   'label' => 'Shifts',    'num' => '02'],
        ['route' => 'site.facility-map',        'label' => 'Facility Map', 'num' => '03'],
        ['route' => 'caretaker.crew.index',     'label' => 'Crew',      'num' => '04'],
        ['route' => 'caretaker.inventory.index','label' => 'Inventory', 'num' => '05'],
        ['route' => 'caretaker.prep.index',     'label' => 'Match Prep','num' => '06'],
    ];
    $active = fn($route) => request()->routeIs(str_replace('.index','.*',$route));
@endphp
<div class="mod-rail rail-caretaker">
    <div class="rail-head"><span class="rail-led"></span>Caretaker Panel<span class="rail-led"></span></div>
    @foreach ($links as $link)
        <a href="{{ route($link['route']) }}" class="mod-link {{ $active($link['route']) ? 'on' : '' }}"><span class="ml-dot"></span><span class="ml-num">{{ $link['num'] }}</span>{{ $link['label'] }}</a>
    @endforeach
    <div class="rail-avatar">
        <div class="portrait mood-ok" style="width:36px;height:36px;font-size:.65rem;border-color:var(--lane-wood)">{{ $initials }}</div>
        <div>
            <span style="font-family:var(--font-sub);font-size:.68rem;color:var(--pin-white);display:block">{{ $u->name }}</span>
            <span style="font-family:var(--font-mono);font-size:.5rem;color:var(--fog);letter-spacing:1px">CARETAKER &middot; LANE CREW</span>
        </div>
    </div>
</div>
