<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">League Office</h2>
            <div style="display:flex;align-items:center;gap:1rem;">
                <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--navy);">{{ $today->format('D, M j Y') }}</span>
                <span class="badge-role manager">Manager</span>
            </div>
        </div>
    </x-slot>

    <style>
        .fixture-card{background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:.9rem 1rem;display:flex;gap:14px;align-items:center;}
        .chk{display:flex;align-items:center;gap:5px;font-family:var(--font-mono);font-size:0.52rem;letter-spacing:.5px;text-transform:uppercase;color:var(--slate);}
        .chk .dot{width:8px;height:8px;border-radius:50%;border:1px solid var(--navy);flex:none;}
    </style>

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div style="padding:1.25rem;overflow:hidden;">

            @if ($live->count())
                <div class="dash-section-label" style="margin-bottom:8px;">Match In Play</div>
                <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:1.25rem;">
                    @foreach ($live as $f)
                        <div class="fixture-card" style="border-color:var(--gold);">
                            <span class="badge-role" style="background:var(--gold-light);color:var(--navy);border:1px solid var(--navy);font-family:var(--font-mono);font-size:0.52rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;">LIVE</span>
                            <div style="flex:1;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                                <span style="font-family:var(--font-header);font-size:0.8rem;color:var(--navy);text-transform:uppercase;">{{ $f->homeTeam->name }}</span>
                                <span style="font-family:var(--font-mono);font-size:0.85rem;font-weight:700;color:var(--navy);">{{ $f->home_score }} — {{ $f->away_score }}</span>
                                <span style="font-family:var(--font-header);font-size:0.8rem;color:var(--navy);text-transform:uppercase;">{{ $f->awayTeam->name }}</span>
                            </div>
                            <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">L{{ $f->lane?->lane_number }} · {{ $f->league->name }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="dash-section-label" style="margin-bottom:8px;">Upcoming Fixtures</div>
            <div style="display:flex;flex-direction:column;gap:10px;">
                @forelse ($upcoming as $row)
                    @php
                        $f = $row['fixture'];
                        $r = $row['ready'];
                        $days = $f->date->startOfDay()->diffInDays($today->startOfDay());
                        $due = $days === 0 ? 'TODAY' : '+' . $days . 'd';
                    @endphp
                    <div class="fixture-card">
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                                <span class="badge-role" style="background:var(--mist);color:var(--slate);border:1px solid var(--fog);font-family:var(--font-mono);font-size:0.52rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;">{{ $f->league->name }}</span>
                                <span style="font-family:var(--font-mono);font-size:0.55rem;color:{{ $due === 'TODAY' ? 'var(--coral)' : 'var(--slate)' }};font-weight:700;">{{ $due }}</span>
                                <span style="font-family:var(--font-header);font-size:0.82rem;color:var(--navy);text-transform:uppercase;">{{ $f->homeTeam->name }}</span>
                                <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">vs</span>
                                <span style="font-family:var(--font-header);font-size:0.82rem;color:var(--navy);text-transform:uppercase;">{{ $f->awayTeam->name }}</span>
                            </div>
                            <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);margin-top:5px;">
                                {{ $f->date->format('M j, Y') }} · {{ $f->time->format('H:i') }} · Lane {{ $f->lane?->lane_number ?? 'TBA' }}
                            </div>
                            <div style="display:flex;gap:12px;margin-top:7px;flex-wrap:wrap;">
                                <span class="chk"><span class="dot" style="background:{{ $r['welcomed'] ? 'var(--ok)' : 'var(--coral)' }};"></span>Welcomed</span>
                                <span class="chk"><span class="dot" style="background:{{ $r['kits'] ? 'var(--ok)' : 'var(--coral)' }};"></span>Kits</span>
                                <span class="chk"><span class="dot" style="background:{{ $r['lane'] ? 'var(--ok)' : 'var(--coral)' }};"></span>Lane</span>
                                <span class="chk"><span class="dot" style="background:{{ $r['training'] ? 'var(--ok)' : 'var(--coral)' }};"></span>Training</span>
                            </div>
                        </div>
                        @unless ($r['welcomed'])
                            <form method="POST" action="{{ route('manager.league.welcome', $f) }}" style="min-width:120px;">
                                @csrf
                                <button type="submit" class="btn-lane primary" style="width:100%;font-size:0.55rem;padding:5px 10px;">Welcome Team</button>
                            </form>
                        @endunless
                    </div>
                @empty
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;text-align:center;">
                        <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">No fixtures on the calendar.</span>
                    </div>
                @endforelse
            </div>

            <div class="dash-section-label" style="margin:1.25rem 0 8px;">League Standings</div>
            <div style="display:grid;grid-template-columns:repeat({{ max(count($leagues), 1) }},1fr);gap:10px;">
                @foreach ($leagues as $league)
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:0.8rem 1rem;">
                        <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">{{ $league->name }}</div>
                        <div style="display:flex;flex-direction:column;gap:5px;">
                            @forelse ($league->teams->sortByDesc(fn ($t) => $t->wins - $t->losses)->take(5) as $team)
                                <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
                                    <span style="font-family:var(--font-sub);font-size:0.68rem;color:var(--navy);">{{ $team->name }}</span>
                                    <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">{{ $team->wins }}-{{ $team->losses }}-{{ $team->draws }}</span>
                                </div>
                            @empty
                                <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">No teams yet.</span>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>

    <x-toast />

    @include('sim.partials.responsive')
</x-app-layout>
