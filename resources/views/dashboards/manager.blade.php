<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Manager Dashboard</h2>
            <div style="display:flex;align-items:center;gap:1rem;">
                <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--navy);">Day {{ $stats['current_day'] }}{{ $stats['bad_day_mode'] ? ' · ⚠ BAD DAY' : '' }}</span>

            </div>
        </div>
    </x-slot>

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        <!-- LEFT SIDEBAR: Module Navigation -->
        @include('sim.partials.manager-sidebar')

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
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:10px;padding-top:10px;border-top:1px solid var(--fog);">
                        <div style="text-align:center;">
                            <div style="font-family:var(--font-mono);font-size:1rem;font-weight:700;color:var(--coral);">{{ $dayReport['turnaways'] ?? 0 }}</div>
                            <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">TURNAWAYS</div>
                        </div>
                        <div style="text-align:center;">
                            <div style="font-family:var(--font-mono);font-size:1rem;font-weight:700;color:var(--coral-dark);">{{ $dayReport['quits'] ?? 0 }}</div>
                            <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">QUITS</div>
                        </div>
                        <div style="text-align:center;">
                            <div style="font-family:var(--font-mono);font-size:1rem;font-weight:700;color:var(--gold);">{{ $dayReport['queues_promoted'] ?? 0 }}</div>
                            <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">QUEUES PROMOTED</div>
                        </div>
                        <div style="text-align:center;">
                            <div style="font-family:var(--font-mono);font-size:1rem;font-weight:700;color:{{ ($dayReport['snitch_bonuses'] ?? 0) > 0 ? 'var(--sky-dark)' : 'var(--slate)' }};">{{ count($dayReport['snitches'] ?? []) }}</div>
                            <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">SNITCH REPORTS</div>
                        </div>
                        <div style="text-align:center;">
                            <div style="font-family:var(--font-mono);font-size:1rem;font-weight:700;color:var(--sky-dark);">{{ $dayReport['matches']?->count() ?? 0 }}</div>
                            <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">MATCHES</div>
                        </div>
                        <div style="text-align:center;">
                            <div style="font-family:var(--font-mono);font-size:1rem;font-weight:700;color:var(--gold);">${{ number_format($dayReport['match_revenue'] ?? 0, 0) }}</div>
                            <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">MATCH REVENUE</div>
                        </div>
                        <div style="text-align:center;">
                            <div style="font-family:var(--font-mono);font-size:1rem;font-weight:700;color:{{ ($dayReport['league_penalties'] ?? 0) > 0 ? 'var(--coral)' : 'var(--sky-dark)' }};">{{ $dayReport['league_penalties'] ?? 0 }}</div>
                            <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">MATCH PREP PENALTIES</div>
                        </div>
                    </div>
                    @php
                        $dayAccidents = $dayReport['accidents'] ?? collect();
                        $sevBadge = ['minor' => 'var(--gold)', 'moderate' => 'var(--coral-dark)', 'major' => 'var(--coral)'];
                        $cancelNote = ($dayReport['accidents_cancelled_bookings'] ?? 0) > 0
                            ? ' · ' . $dayReport['accidents_cancelled_bookings'] . ' booking(s) cancelled'
                            : '';
                    @endphp
                    @if ($dayAccidents->isNotEmpty())
                        <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--fog);">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                                <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--coral);letter-spacing:1px;">ACCIDENT REPORT &middot; {{ $dayAccidents->count() }} ON THE FLOOR</span>
                                <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">COST ${{ number_format($dayReport['accident_cost'] ?? 0, 0) }}{{ $cancelNote }}</span>
                            </div>
                            <div style="display:flex;flex-direction:column;gap:6px;">
                                @foreach ($dayAccidents as $a)
                                    <div style="background:var(--pin-white);border:1px solid var(--navy);border-radius:8px;padding:7px 10px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                                        <span style="font-family:var(--font-mono);font-size:0.5rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:{{ $sevBadge[$a['severity']] ?? 'var(--slate)' }};color:var(--pin-white);">{{ strtoupper($a['severity']) }}</span>
                                        <span style="font-family:var(--font-sub);font-size:0.68rem;color:var(--navy);min-width:110px;">{{ $a['staff_name'] }}</span>
                                        <span style="font-family:var(--font-body);font-size:0.66rem;color:var(--slate);flex:1;min-width:160px;">{{ $a['description'] }}</span>
                                        <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--coral);">-${{ $a['cost'] }}</span>
                                        <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--sky-dark);">mood {{ $a['happiness_change'] >= 0 ? '+' : '' }}{{ $a['happiness_change'] }}</span>
                                        @if (($a['inventory_damage'] ?? 0) > 0)
                                            <span style="font-family:var(--font-mono);font-size:0.5rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:var(--mist);color:var(--navy);border:1px solid var(--fog);">stock -{{ $a['inventory_damage'] }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--fog);">
                            <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">ACCIDENT REPORT · Clean floor today.</span>
                        </div>
                    @endif
                    @if (count($dayReport['matches'] ?? []))
                        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:10px;padding-top:10px;border-top:1px solid var(--fog);">
                            @foreach (collect($dayReport['matches'])->take(4) as $m)
                                <div style="text-align:center;">
                                    <div style="font-family:var(--font-mono);font-size:0.6rem;font-weight:700;color:{{ $m['status'] === 'live' ? 'var(--gold)' : 'var(--sky-dark)' }};text-transform:uppercase;">{{ $m['status'] }}</div>
                                    <div style="font-family:var(--font-sub);font-size:0.6rem;color:var(--navy);margin-top:2px;">{{ $m['label'] }}</div>
                                    <div style="font-family:var(--font-mono);font-size:0.65rem;font-weight:700;color:var(--navy);">{{ $m['home_score'] }} — {{ $m['away_score'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <!-- Incident Log (persistent — survives day toggles) -->
            @php
                $logSev = ['minor' => 'var(--gold)', 'moderate' => 'var(--coral-dark)', 'major' => 'var(--coral)'];
            @endphp
            <div style="margin-bottom:1.25rem;">
                <div class="dash-section-label" style="margin-bottom:6px;">Incident Log &middot; Last {{ $recentIncidents->count() }} on Record</div>
                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;padding:12px;display:flex;flex-direction:column;gap:6px;">
                    @forelse ($recentIncidents as $incident)
                        <div style="display:flex;align-items:center;gap:8px;border-bottom:1px dashed var(--fog);padding:4px 0;flex-wrap:wrap;">
                            <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);min-width:70px;">{{ $incident->created_at->format('M j, H:i') }}</span>
                            <span style="font-family:var(--font-mono);font-size:0.5rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:{{ $logSev[$incident->severity] ?? 'var(--slate)' }};color:var(--pin-white);">{{ strtoupper($incident->severity) }}</span>
                            <span style="font-family:var(--font-sub);font-size:0.68rem;color:var(--navy);">{{ $incident->staff?->user?->name ?? 'Staff' }}</span>
                            <span style="font-family:var(--font-body);font-size:0.62rem;color:var(--slate);flex:1;min-width:140px;">{{ $incident->description ?? ucfirst(str_replace('_', ' ', $incident->type)) }}</span>
                            <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--sky-dark);">{{ $incident->shift?->date ? \Carbon\Carbon::parse($incident->shift->date)->format('M j') : '' }}</span>
                            <span class="badge {{ $incident->resolved ? 'ok' : 'coral' }}" style="font-size:0.5rem;">{{ $incident->resolved ? 'RESOLVED' : 'OPEN' }}</span>
                        </div>
                    @empty
                        <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">No incidents on record. Spotless floor.</span>
                    @endforelse
                </div>
            </div>

            <!-- Recent Events -->
            @if($recentEvents->count())
                <div>
                    <div class="dash-section-label" style="margin-bottom:6px;">Recent Staff Events</div>
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;padding:12px;font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);line-height:1.8;">
                        @foreach($recentEvents->take(6) as $event)
                            <div>
                                <span style="color:var(--navy);font-weight:700;">{{ $event->staff->user->name ?? 'Staff' }}</span>
                                · {{ \App\Helpers\Label::staffEventType($event->event_type) }}
                                @if($event->description) — {{ $event->description }} @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Break Room Chatter -->
            <div style="margin-top:1.25rem;">
                <div class="dash-section-label" style="margin-bottom:6px;">Break Room Chatter</div>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px;">
                    @foreach($chatter as $chat)
                        <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;padding:12px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                <span style="font-family:var(--font-sub);font-size:0.72rem;color:var(--navy);font-weight:700;">{{ $chat['name'] }}</span>
                                <span style="font-family:var(--font-mono);font-size:0.55rem;color:{{ $chat['happiness'] < 50 ? 'var(--coral)' : 'var(--sky-dark)' }};">{{ $chat['role'] }} · {{ $chat['happiness'] }}%</span>
                            </div>
                            @include('sim.partials.dialogue', ['bubbles' => $chat['bubbles']])
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    <x-toast />

    @include('sim.partials.responsive')
</x-app-layout>
