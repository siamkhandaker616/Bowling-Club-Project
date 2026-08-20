@php
    $u = auth()->user();
    $initials = strtoupper(substr($u->name,0,1)) . strtoupper(substr(str_replace(' ','',$u->name),-1));
    $links = [
        ['route' => 'steward.dashboard',      'label' => 'Overview',   'num' => '01'],
        ['route' => 'steward.schedule.index', 'label' => 'Schedule',   'num' => '02'],
        ['route' => 'steward.visitors.index', 'label' => 'Visitors',   'num' => '03'],
        ['route' => 'site.facility-map',      'label' => 'Facility Map', 'num' => '04'],
        ['route' => 'steward.complaints.index','label' => 'Complaints','num' => '05'],
        ['route' => 'steward.incidents.index', 'label' => 'Incidents',  'num' => '06'],
        ['route' => 'steward.bans.index',      'label' => 'Bans',       'num' => '07'],
        ['route' => 'steward.snitch.index',    'label' => 'Snitch Inbox','num' => '08'],
        ['route' => 'steward.payroll.index',   'label' => 'Payroll',    'num' => '09'],
    ];
    $active = fn($route) => request()->routeIs(str_replace('.index','.*',$route));
@endphp
<div class="mod-rail rail-steward">
    <div class="rail-head"><span class="rail-led"></span>Steward Panel<span class="rail-led"></span></div>
    @foreach ($links as $link)
        <a href="{{ route($link['route']) }}" class="mod-link {{ $active($link['route']) ? 'on' : '' }}"><span class="ml-dot"></span><span class="ml-num">{{ $link['num'] }}</span>{{ $link['label'] }}</a>
    @endforeach
    <div class="rail-avatar">
        <div class="portrait mood-happy" style="width:36px;height:36px;font-size:.65rem;border-color:var(--coral)">{{ $initials }}</div>
        <div>
            <span style="font-family:var(--font-sub);font-size:.68rem;color:var(--pin-white);display:block">{{ $u->name }}</span>
            <span style="font-family:var(--font-mono);font-size:.5rem;color:var(--fog);letter-spacing:1px">STEWARD</span>
        </div>
    </div>
</div>
