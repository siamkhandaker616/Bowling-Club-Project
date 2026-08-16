<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Confrontations</h2>
            <div style="display:flex;align-items:center;gap:1rem;">
                <span class="badge-role manager">Manager</span>
            </div>
        </div>
    </x-slot>

    <style>
        .con-card{background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;}
        .con-badge{font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;}
        .con-party{flex:1;background:var(--pin-white);border-radius:8px;padding:8px 10px;border:1px solid var(--fog);}
        .con-arrow{font-family:var(--font-mono);font-size:1rem;color:var(--coral);align-self:center;}
        .con-form{display:grid;grid-template-columns:1fr 1fr 1fr 2fr;gap:8px;margin-top:8px;}
        .con-input{font-family:var(--font-body);font-size:0.7rem;padding:6px 8px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);}
    </style>

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div style="padding:1.25rem;overflow:hidden;">

            <div class="con-card" style="margin-bottom:1.25rem;">
                <div class="dash-section-label">Log a Confrontation</div>
                <form method="POST" action="{{ route('manager.confrontations.store') }}" class="con-form">
                    @csrf
                    <select name="reporter_staff_id" required class="con-input fold-select">
                        <option value="" disabled selected>Reporter…</option>
                        @foreach ($activeStaff as $s)
                            <option value="{{ $s->id }}">{{ $s->user->name }}</option>
                        @endforeach
                    </select>
                    <select name="accused_staff_id" required class="con-input fold-select">
                        <option value="" disabled selected>Accused…</option>
                        @foreach ($activeStaff as $s)
                            <option value="{{ $s->id }}">{{ $s->user->name }}</option>
                        @endforeach
                    </select>
                    <select name="incident_type" required class="con-input fold-select">
                        <option value="theft">Theft</option>
                        <option value="sabotage">Sabotage</option>
                        <option value="harassment">Harassment</option>
                        <option value="negligence">Negligence</option>
                        <option value="other">Other</option>
                    </select>
                    <div style="display:flex;gap:8px;">
                        <input name="incident_description" type="text" placeholder="What happened…" style="flex:1;" class="con-input">
                        <label class="pin-check" style="font-family:var(--font-mono);font-size:0.55rem;color:var(--navy);cursor:pointer;">
                            <input type="checkbox" name="db_verified" value="1"><span class="pin-box"></span> DB Verified
                        </label>
                        <button type="submit" class="btn-lane primary" style="font-size:0.55rem;padding:5px 12px;">Log</button>
                    </div>
                </form>
            </div>

            <div style="display:flex;flex-direction:column;gap:10px;">
                @forelse ($confrontations as $confrontation)
                    <div class="con-card">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <div style="display:flex;gap:8px;align-items:center;">
                                <span class="con-badge" style="background:var(--navy);color:var(--pin-white);">{{ $confrontation->incident_type }}</span>
                                @if ($confrontation->db_verified)
                                    <span class="con-badge" style="background:var(--sky);color:var(--sky-dark);border:1px solid var(--sky-dark);">VERIFIED</span>
                                @endif
                                <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">{{ $confrontation->date }}</span>
                            </div>
                            <span class="con-badge" style="background:{{ $confrontation->manager_verdict ? 'var(--mist)' : 'var(--gold-light)' }};color:var(--navy);border:1px solid var(--navy);">{{ $confrontation->manager_verdict ?? 'awaiting verdict' }}</span>
                        </div>

                        <div style="display:flex;gap:14px;margin-top:10px;">
                            <div class="con-party">
                                <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">REPORTER</div>
                                <div style="font-family:var(--font-sub);font-size:0.72rem;">{{ $confrontation->reporter->user->name }}</div>
                            </div>
                            <div class="con-arrow">&#8594;</div>
                            <div class="con-party">
                                <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">ACCUSED</div>
                                <div style="font-family:var(--font-sub);font-size:0.72rem;">{{ $confrontation->accused->user->name }}</div>
                            </div>
                        </div>

                        @if ($confrontation->incident_description)
                            <div style="font-family:var(--font-body);font-size:0.7rem;margin-top:8px;background:var(--mist);border-radius:8px;padding:8px 10px;">{{ $confrontation->incident_description }}</div>
                        @endif

                        @if ($confrontation->investigation_result)
                            <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--sky-dark);margin-top:8px;">{{ $confrontation->investigation_result }}</div>
                        @endif

                        @if (! $confrontation->staff_response)
                            <form method="POST" action="{{ route('manager.confrontations.respond', $confrontation) }}" style="display:flex;gap:8px;margin-top:10px;align-items:center;">
                                @csrf
                                <select name="staff_response" required class="con-input fold-select">
                                    <option value="confessed">Confessed</option>
                                    <option value="innocent">Pleads Innocent</option>
                                    <option value="bs">Calls BS</option>
                                </select>
                                <button type="submit" class="btn-lane secondary" style="font-size:0.55rem;padding:5px 12px;">Record Response</button>
                                <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">or</span>
                                <button type="submit" name="auto" value="1" formnovalidate class="btn-lane primary" style="font-size:0.55rem;padding:5px 12px;">Auto-Investigate</button>
                                <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">(honesty-weighted confession + DB evidence)</span>
                            </form>
                        @elseif (! $confrontation->manager_verdict)
                            <form method="POST" action="{{ route('manager.confrontations.verdict', $confrontation) }}" style="display:flex;gap:8px;margin-top:10px;align-items:center;">
                                @csrf
                                <select name="verdict" required class="con-input fold-select">
                                    <option value="upheld">Upheld</option>
                                    <option value="dismissed">Dismissed</option>
                                    <option value="penalized">Penalized</option>
                                </select>
                                <input name="penalty_amount" type="number" step="0.01" min="0" placeholder="Penalty $" style="width:120px;" class="con-input" data-stepper="edit">
                                <button type="submit" class="btn-lane primary" style="font-size:0.55rem;padding:5px 12px;">Apply Verdict</button>
                                <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">Accused responded: {{ $confrontation->staff_response }}</span>
                            </form>
                        @endif
                    </div>
                @empty
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;text-align:center;">
                        <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">No confrontations yet.</span>
                    </div>
                @endforelse
            </div>

        </div>
    </div>

    <x-toast />

    @include('sim.partials.fold-controls')
    @include('sim.partials.responsive')
</x-app-layout>
