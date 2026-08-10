<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Manager Dashboard</h2>
            <div style="display:flex;align-items:center;gap:1rem;">
                <span class="badge-role manager">Manager</span>
            </div>
        </div>
    </x-slot>

    <div style="zoom:1.25;display:grid;grid-template-columns:200px 1fr;gap:0;min-height:calc(100vh - 200px);">

        <!-- LEFT SIDEBAR: Module Navigation -->
        <div class="dash-sidebar">
            <div class="dash-section-label" style="margin-bottom:4px;">Modules</div>
            <a href="#" class="dash-sidebar-link active">&#127918; Overview</a>
            <a href="#" class="dash-sidebar-link">&#128101; Staff</a>
            <a href="#" class="dash-sidebar-link">&#128230; Inventory</a>
            <a href="#" class="dash-sidebar-link">&#128197; Fixtures</a>
            <a href="#" class="dash-sidebar-link">&#127903; Bookings</a>
            <a href="{{ route('site.announcements.index') }}" class="dash-sidebar-link">&#128227; Announcements</a>
            <a href="#" class="dash-sidebar-link">&#9878; Complaints</a>
            <a href="#" class="dash-sidebar-link">&#9881; Settings</a>
            <div style="margin-top:auto;padding-top:0.75rem;border-top:2px solid var(--fog);text-align:center;">
                <div class="ball-avatar ball-sm ball-navy" style="margin:0 auto;"><div class="ball-holes"><span></span><span></span><span></span></div><span class="ball-initials">SK</span></div>
                <div style="font-family:var(--font-sub);font-size:0.65rem;margin-top:4px;">{{ ucfirst(Auth::user()->name) }}</div>
                <span class="badge-role manager" style="font-size:0.5rem;padding:2px 8px;">Manager</span>
            </div>
        </div>

        <!-- CENTER CONTENT -->
        <div style="padding:1.25rem;overflow:hidden;">

            <!-- Lane Status Overhead -->
            <div style="margin-bottom:1.25rem;">
                <div class="dash-section-label" style="margin-bottom:6px;">Lane Status Overhead</div>
                <div style="display:grid;grid-template-columns:repeat({{ min($stats['total_lanes'], 6) }},1fr);gap:6px;">
                    @php
                        $laneStatuses = [
                            ['pct' => 85, 'status' => 'OPEN', 'color' => 'var(--sky-dark)', 'bg' => 'var(--sky-light)', 'border' => 'var(--navy)'],
                            ['pct' => 100, 'status' => 'BUSY', 'color' => 'var(--gold)', 'bg' => 'var(--sky-light)', 'border' => 'var(--gold)'],
                            ['pct' => 0, 'status' => 'JAMMED', 'color' => 'var(--coral-dark)', 'bg' => 'var(--coral-light)', 'border' => 'var(--coral)'],
                            ['pct' => 90, 'status' => 'BUSY', 'color' => 'var(--sky-dark)', 'bg' => 'var(--sky-light)', 'border' => 'var(--navy)'],
                            ['pct' => 100, 'status' => 'BUSY', 'color' => 'var(--gold)', 'bg' => 'var(--sky-light)', 'border' => 'var(--gold)'],
                            ['pct' => 45, 'status' => 'OIL LOW', 'color' => 'var(--lane-wood)', 'bg' => 'var(--sky-light)', 'border' => 'var(--navy)'],
                        ];
                    @endphp
                    @for($i = 0; $i < min($stats['total_lanes'], 6); $i++)
                        @php $ls = $laneStatuses[$i]; @endphp
                        <div style="background:{{ $ls['bg'] }};border:2px solid {{ $ls['border'] }};border-radius:8px;padding:8px;text-align:center;">
                            <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">L{{ $i + 1 }}</div>
                            <div style="height:40px;display:flex;align-items:flex-end;justify-content:center;margin:4px 0;">
                                <div style="width:16px;height:{{ round($ls['pct'] * 0.4) }}px;background:{{ $ls['color'] }};border-radius:2px 2px 0 0;"></div>
                            </div>
                            <div style="font-family:var(--font-mono);font-size:0.7rem;color:{{ $ls['color'] }};">{{ $ls['pct'] }}%</div>
                            <div style="font-family:var(--font-mono);font-size:0.5rem;color:{{ $ls['status'] === 'JAMMED' ? 'var(--coral-dark)' : ($ls['status'] === 'BUSY' && $ls['color'] === 'var(--gold)' ? 'var(--gold)' : 'var(--slate)') }};">{{ $ls['status'] }}</div>
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Revenue Scorecard -->
            <div style="margin-bottom:1.25rem;">
                <div class="dash-section-label" style="margin-bottom:6px;">Club Revenue Scorecard</div>
                <div class="scorecard" style="width:100%;">
                    <div class="sc-frame" style="flex:1;">
                        <div class="sc-num">Revenue</div>
                        <div class="sc-rolls" style="flex-direction:column;">
                            <span class="sc-roll strike" style="font-size:1rem;">$12.4k</span>
                        </div>
                        <div class="sc-total" style="font-size:0.75rem;">F1</div>
                    </div>
                    <div class="sc-frame" style="flex:1;">
                        <div class="sc-num">Expenses</div>
                        <div class="sc-rolls" style="flex-direction:column;">
                            <span class="sc-roll" style="font-size:1rem;color:var(--coral);">-$3.1k</span>
                        </div>
                        <div class="sc-total" style="font-size:0.75rem;">F2</div>
                    </div>
                    <div class="sc-frame" style="flex:1;">
                        <div class="sc-num">Repairs</div>
                        <div class="sc-rolls" style="flex-direction:column;">
                            <span class="sc-roll spare" style="font-size:1rem;">88%</span>
                        </div>
                        <div class="sc-total" style="font-size:0.75rem;">F3</div>
                    </div>
                    <div class="sc-frame" style="flex:1;">
                        <div class="sc-num">Requests</div>
                        <div class="sc-rolls" style="flex-direction:column;">
                            <span class="sc-roll" style="font-size:1rem;">3</span>
                        </div>
                        <div class="sc-total" style="font-size:0.75rem;">F4</div>
                    </div>
                    <div class="sc-frame" style="flex:1.2;">
                        <div class="sc-num">Net</div>
                        <div class="sc-rolls" style="flex-direction:column;">
                            <span class="sc-roll strike" style="font-size:1.1rem;">$9.3k</span>
                        </div>
                        <div class="sc-total">TOTAL</div>
                    </div>
                </div>
            </div>

            <!-- Inventory Status -->
            <div>
                <div class="dash-section-label" style="margin-bottom:6px;">Inventory Status</div>
                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;padding:14px 16px;display:grid;grid-template-columns:repeat(2,1fr);gap:12px 24px;">
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                            <span style="font-family:var(--font-sub);font-size:0.7rem;">&#128098; Bowling Shoes</span>
                            <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--gold);">42/50</span>
                        </div>
                        <div style="height:8px;background:var(--fog);border-radius:4px;overflow:hidden;">
                            <div style="width:84%;height:100%;background:var(--gold);border-radius:4px;"></div>
                        </div>
                    </div>
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                            <span style="font-family:var(--font-sub);font-size:0.7rem;">&#128167; Lane Oil</span>
                            <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--coral);">12% LOW</span>
                        </div>
                        <div style="height:8px;background:var(--fog);border-radius:4px;overflow:hidden;">
                            <div style="width:12%;height:100%;background:var(--coral);border-radius:4px;"></div>
                        </div>
                    </div>
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                            <span style="font-family:var(--font-sub);font-size:0.7rem;">&#127866; Beer Kegs</span>
                            <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--sky-dark);">8/10</span>
                        </div>
                        <div style="height:8px;background:var(--fog);border-radius:4px;overflow:hidden;">
                            <div style="width:80%;height:100%;background:var(--sky-dark);border-radius:4px;"></div>
                        </div>
                    </div>
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                            <span style="font-family:var(--font-sub);font-size:0.7rem;">&#127936; Spare Pins</span>
                            <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--lane-wood);">30 units</span>
                        </div>
                        <div style="height:8px;background:var(--fog);border-radius:4px;overflow:hidden;">
                            <div style="width:60%;height:100%;background:var(--lane-wood);border-radius:4px;"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
