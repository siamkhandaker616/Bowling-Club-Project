<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Manager Dashboard</h2>
            <div style="display:flex;align-items:center;gap:1rem;">
                <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--navy);">Day {{ $stats['current_day'] }}{{ $stats['bad_day_mode'] ? ' · ⚠ BAD DAY' : '' }}</span>
                <span class="badge-role manager">Manager</span>
            </div>
        </div>
    </x-slot>

    <div style="zoom:1.25;display:grid;grid-template-columns:200px 1fr;gap:0;min-height:calc(100vh - 200px);">

        <!-- LEFT SIDEBAR: Module Navigation -->
        <div class="dash-sidebar">
            <div class="dash-section-label" style="margin-bottom:4px;">Modules</div>
            <a href="{{ route('manager.dashboard') }}" class="dash-sidebar-link active">&#127918; Overview</a>
            <a href="{{ route('manager.staff.index') }}" class="dash-sidebar-link">&#128101; Staff</a>
            <a href="{{ route('manager.inventory.index') }}" class="dash-sidebar-link">&#128230; Inventory</a>
            <a href="{{ route('public.fixtures') }}" class="dash-sidebar-link">&#128197; Fixtures</a>
            <a href="{{ route('manager.bookings.index') }}" class="dash-sidebar-link">&#127903; Bookings</a>
            <a href="{{ route('site.announcements.index') }}" class="dash-sidebar-link">&#128227; Announcements</a>
            <a href="{{ route('manager.complaints.index') }}" class="dash-sidebar-link">&#9878; Complaints</a>
            <a href="{{ route('manager.confrontations.index') }}" class="dash-sidebar-link">&#9881; Confrontations</a>
            <a href="{{ route('manager.bans.index') }}" class="dash-sidebar-link">&#128683; Bans</a>
            <a href="{{ route('manager.reviews.index') }}" class="dash-sidebar-link">&#11088; Reviews</a>
            <a href="{{ route('manager.touring.index') }}" class="dash-sidebar-link">&#128742; Touring</a>
            <div style="margin-top:auto;padding-top:0.75rem;border-top:2px solid var(--fog);text-align:center;">
                <div class="ball-avatar ball-sm ball-navy" style="margin:0 auto;"><div class="ball-holes"><span></span><span></span><span></span></div><span class="ball-initials">SK</span></div>
                <div style="font-family:var(--font-sub);font-size:0.65rem;margin-top:4px;">{{ ucfirst($user->name) }}</div>
                <span class="badge-role manager" style="font-size:0.5rem;padding:2px 8px;">Manager</span>
            </div>
        </div>

        <!-- CENTER CONTENT -->
        <div style="padding:1.25rem;overflow:hidden;">

            <!-- Lane Status Overhead -->
            <div style="margin-bottom:1.25rem;">
                <div class="dash-section-label" style="margin-bottom:6px;">Lane Status Overhead</div>
                <div style="display:grid;grid-template-columns:repeat({{ min($stats['total_lanes'], 6) }},1fr);gap:6px;">
                    @foreach($lanes->take(6) as $lane)
                        @php
                            $oil = $lane->oil_level ?? 0;
                            if ($lane->status === 'maintenance' || $lane->status === 'reserved') {
                                $status = strtoupper($lane->status);
                                $color = 'var(--coral-dark)';
                                $bg = 'var(--coral-light)';
                                $border = 'var(--coral)';
                            } elseif ($lane->status === 'occupied') {
                                $status = 'BUSY';
                                $color = 'var(--gold)';
                                $bg = 'var(--sky-light)';
                                $border = 'var(--gold)';
                            } elseif ($oil < 20) {
                                $status = 'OIL LOW';
                                $color = 'var(--lane-wood)';
                                $bg = 'var(--sky-light)';
                                $border = 'var(--navy)';
                            } else {
                                $status = 'OPEN';
                                $color = 'var(--sky-dark)';
                                $bg = 'var(--sky-light)';
                                $border = 'var(--navy)';
                            }
                        @endphp
                        <div style="background:{{ $bg }};border:2px solid {{ $border }};border-radius:8px;padding:8px;text-align:center;">
                            <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">L{{ $lane->lane_number }}</div>
                            <div style="height:40px;display:flex;align-items:flex-end;justify-content:center;margin:4px 0;">
                                <div style="width:16px;height:{{ round($oil * 0.4) }}px;background:{{ $color }};border-radius:2px 2px 0 0;"></div>
                            </div>
                            <div style="font-family:var(--font-mono);font-size:0.7rem;color:{{ $color }};">{{ $oil }}%</div>
                            <div style="font-family:var(--font-mono);font-size:0.5rem;color:{{ $status === 'OIL LOW' ? 'var(--gold)' : ($status === 'BUSY' ? 'var(--gold)' : 'var(--slate)') }};">{{ $status }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Revenue Scorecard -->
            <div style="margin-bottom:1.25rem;">
                <div class="dash-section-label" style="margin-bottom:6px;">Club Revenue Scorecard</div>
                <div class="scorecard" style="width:100%;">
                    <div class="sc-frame" style="flex:1;">
                        <div class="sc-num">Revenue</div>
                        <div class="sc-rolls" style="flex-direction:column;">
                            <span class="sc-roll strike" style="font-size:1rem;">${{ number_format($stats['total_revenue'], 0) }}</span>
                        </div>
                        <div class="sc-total" style="font-size:0.75rem;">F1</div>
                    </div>
                    <div class="sc-frame" style="flex:1;">
                        <div class="sc-num">Expenses</div>
                        <div class="sc-rolls" style="flex-direction:column;">
                            <span class="sc-roll" style="font-size:1rem;color:var(--coral);">-${{ number_format($stats['total_expenses'], 0) }}</span>
                        </div>
                        <div class="sc-total" style="font-size:0.75rem;">F2</div>
                    </div>
                    <div class="sc-frame" style="flex:1;">
                        <div class="sc-num">Reputation</div>
                        <div class="sc-rolls" style="flex-direction:column;">
                            <span class="sc-roll spare" style="font-size:1rem;">{{ $stats['reputation'] }}%</span>
                        </div>
                        <div class="sc-total" style="font-size:0.75rem;">F3</div>
                    </div>
                    <div class="sc-frame" style="flex:1;">
                        <div class="sc-num">Complaints</div>
                        <div class="sc-rolls" style="flex-direction:column;">
                            <span class="sc-roll" style="font-size:1rem;">{{ $stats['pending_complaints'] }}</span>
                        </div>
                        <div class="sc-total" style="font-size:0.75rem;">F4</div>
                    </div>
                    <div class="sc-frame" style="flex:1.2;">
                        <div class="sc-num">Net</div>
                        <div class="sc-rolls" style="flex-direction:column;">
                            <span class="sc-roll strike" style="font-size:1.1rem;">${{ number_format($stats['net'], 0) }}</span>
                        </div>
                        <div class="sc-total">TOTAL</div>
                    </div>
                </div>
            </div>

            <!-- Inventory Status -->
            <div style="margin-bottom:1.25rem;">
                <div class="dash-section-label" style="margin-bottom:6px;">Inventory Status</div>
                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;padding:14px 16px;display:grid;grid-template-columns:repeat(2,1fr);gap:12px 24px;">
                    @foreach($lanes->take(4) as $i => $lane)
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                                <span style="font-family:var(--font-sub);font-size:0.7rem;">Lane {{ $lane->lane_number }} Oil</span>
                                <span style="font-family:var(--font-mono);font-size:0.65rem;color:{{ $lane->oil_level < 20 ? 'var(--coral)' : 'var(--sky-dark)' }};">{{ $lane->oil_level }}%</span>
                            </div>
                            <div style="height:8px;background:var(--fog);border-radius:4px;overflow:hidden;">
                                <div style="width:{{ $lane->oil_level }}%;height:100%;background:{{ $lane->oil_level < 20 ? 'var(--coral)' : ($lane->oil_level < 50 ? 'var(--gold)' : 'var(--sky-dark)') }};border-radius:4px;"></div>
                            </div>
                        </div>
                    @endforeach
                    @foreach($lowStock->take(4) as $item)
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                                <span style="font-family:var(--font-sub);font-size:0.7rem;">{{ $item->name }}</span>
                                <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--coral);">{{ $item->quantity }} LOW</span>
                            </div>
                            <div style="height:8px;background:var(--fog);border-radius:4px;overflow:hidden;">
                                <div style="width:{{ min(100, ($item->quantity / max($item->reorder_threshold * 2, 1)) * 100) }}%;height:100%;background:var(--coral);border-radius:4px;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- End-of-Day Report (if available) -->
            @if($dayReport)
                <div style="margin-bottom:1.25rem;">
                    <div class="dash-section-label" style="margin-bottom:6px;">End-of-Day Report — {{ $dayReport['date_label'] ?? '' }}</div>
                    <div style="background:var(--sky-light);border:2px solid var(--gold);border-radius:10px;padding:14px 16px;display:grid;grid-template-columns:repeat(4,1fr);gap:12px;">
                        <div style="text-align:center;">
                            <div style="font-family:var(--font-mono);font-size:1.1rem;font-weight:700;color:var(--navy);">{{ $dayReport['bookings_served'] ?? 0 }}</div>
                            <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">BOOKINGS SERVED</div>
                        </div>
                        <div style="text-align:center;">
                            <div style="font-family:var(--font-mono);font-size:1.1rem;font-weight:700;color:var(--gold);">${{ number_format($dayReport['revenue'] ?? 0, 0) }}</div>
                            <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">REVENUE</div>
                        </div>
                        <div style="text-align:center;">
                            <div style="font-family:var(--font-mono);font-size:1.1rem;font-weight:700;color:var(--coral);">${{ number_format($dayReport['expenses'] ?? 0, 0) }}</div>
                            <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">EXPENSES</div>
                        </div>
                        <div style="text-align:center;">
                            <div style="font-family:var(--font-mono);font-size:1.1rem;font-weight:700;color:{{ ($dayReport['reputation_delta'] ?? 0) >= 0 ? 'var(--sky-dark)' : 'var(--coral)' }};">{{ ($dayReport['reputation_delta'] ?? 0) >= 0 ? '+' : '' }}{{ $dayReport['reputation_delta'] ?? 0 }}</div>
                            <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">REPUTATION</div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Recent Events -->
            @if($recentEvents->count())
                <div>
                    <div class="dash-section-label" style="margin-bottom:6px;">Recent Staff Events</div>
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;padding:12px;font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);line-height:1.8;">
                        @foreach($recentEvents->take(6) as $event)
                            <div>
                                <span style="color:var(--navy);font-weight:700;">{{ $event->staff->user->name ?? 'Staff' }}</span>
                                · {{ ucfirst(str_replace('_', ' ', $event->event_type)) }}
                                @if($event->description) — {{ $event->description }} @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
