<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Match Prep</h2>
            <div style="display:flex;align-items:center;gap:1rem;">
                <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--navy);">{{ $today->format('D, M j Y') }}</span>
                <span class="badge-role caretaker">Caretaker</span>
            </div>
        </div>
    </x-slot>

    <style>
        .prep-card{background:var(--sky-light);border:2px solid var(--lane-wood);border-radius:12px;padding:.9rem 1rem;display:flex;gap:14px;align-items:center;}
        .chk{display:flex;align-items:center;gap:5px;font-family:var(--font-mono);font-size:0.52rem;letter-spacing:.5px;text-transform:uppercase;color:var(--slate);}
        .chk .dot{width:8px;height:8px;border-radius:50%;border:1px solid var(--navy);flex:none;}
        .btn-lane[disabled]{opacity:.45;cursor:not-allowed}
    </style>

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div style="padding:1.25rem;overflow:hidden;">

            @if ($lowStock->count())
                <div style="background:var(--gold-light);border:2px solid var(--gold);border-radius:10px;padding:8px 12px;margin-bottom:1rem;display:flex;align-items:center;gap:8px;">
                    <span style="font-family:var(--font-mono);font-size:0.6rem;font-weight:700;color:var(--navy);">LOW STOCK:</span>
                    <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">
                        {{ $lowStock->pluck('name')->join(', ') }}
                    </span>
                    <a href="{{ route('caretaker.inventory.index') }}" class="btn-lane solid" style="margin-left:auto;font-size:0.5rem;padding:4px 10px;text-decoration:none;">Restock</a>
                </div>
            @endif

            <div class="dash-section-label" style="margin-bottom:8px;">Prep Queue — next {{ \App\Services\Simulation\MatchService::PREP_WINDOW_DAYS }} days</div>
            <div style="display:flex;flex-direction:column;gap:10px;">
                @forelse ($fixtures as $row)
                    @php
                        $f = $row['fixture'];
                        $r = $row['ready'];
                        $days = $f->date->startOfDay()->diffInDays($today->startOfDay());
                        $due = $days === 0 ? 'TODAY' : '+' . $days . 'd';
                    @endphp
                    <div class="prep-card">
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                                <span style="font-family:var(--font-mono);font-size:0.55rem;color:{{ $due === 'TODAY' ? 'var(--coral)' : 'var(--slate)' }};font-weight:700;">{{ $due }}</span>
                                <span style="font-family:var(--font-header);font-size:0.8rem;color:var(--navy);text-transform:uppercase;">{{ $f->homeTeam->name }}</span>
                                <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">vs</span>
                                <span style="font-family:var(--font-header);font-size:0.8rem;color:var(--navy);text-transform:uppercase;">{{ $f->awayTeam->name }}</span>
                            </div>
                            <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);margin-top:4px;">
                                {{ $f->date->format('M j, Y') }} · {{ $f->time->format('H:i') }} · Lane {{ $f->lane?->lane_number ?? 'TBA' }} · {{ $f->league->name }}
                            </div>
                            <div style="display:flex;gap:12px;margin-top:7px;flex-wrap:wrap;">
                                <span class="chk"><span class="dot" style="background:{{ $r['kits'] ? 'var(--ok)' : 'var(--coral)' }};"></span>Kits</span>
                                <span class="chk"><span class="dot" style="background:{{ $r['lane'] ? 'var(--ok)' : 'var(--coral)' }};"></span>Lane</span>
                                <span class="chk"><span class="dot" style="background:{{ $r['training'] ? 'var(--ok)' : 'var(--coral)' }};"></span>Training</span>
                            </div>
                        </div>
                        <div style="display:flex;flex-direction:column;gap:6px;min-width:130px;">
                            @unless ($r['kits'])
                                <form method="POST" action="{{ route('caretaker.prep.prepare', [$f, 'kits']) }}">
                                    @csrf
                                    <button type="submit" class="btn-lane primary" style="width:100%;font-size:0.55rem;padding:5px 10px;">Prep Kits</button>
                                </form>
                            @endunless
                            @unless ($r['lane'])
                                <form method="POST" action="{{ route('caretaker.prep.prepare', [$f, 'lane']) }}">
                                    @csrf
                                    <button type="submit" class="btn-lane solid" style="width:100%;font-size:0.55rem;padding:5px 10px;">Prep Lane</button>
                                </form>
                            @endunless
                            @unless ($r['training'])
                                <form method="POST" action="{{ route('caretaker.prep.prepare', [$f, 'training']) }}">
                                    @csrf
                                    <button type="submit" class="btn-lane secondary" style="width:100%;font-size:0.55rem;padding:5px 10px;">Training</button>
                                </form>
                            @endunless
                        </div>
                    </div>
                @empty
                    <div style="background:var(--sky-light);border:2px solid var(--lane-wood);border-radius:12px;padding:1rem;text-align:center;">
                        <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">Nothing in the prep window. The lanes stay quiet.</span>
                    </div>
                @endforelse
            </div>

        </div>
    </div>

    <x-toast />

    @include('sim.partials.responsive')
</x-app-layout>
