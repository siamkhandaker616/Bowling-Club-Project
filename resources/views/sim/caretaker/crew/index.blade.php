<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Crew Relations</h2>
            <span class="badge-role caretaker">Caretaker</span>
        </div>
    </x-slot>

    <div style="zoom:1.25;display:grid;grid-template-columns:180px 1fr 220px;gap:0;min-height:calc(100vh - 200px);">
        <div style="background:var(--sky-light);border-right:3px solid var(--navy);padding:1rem;display:flex;flex-direction:column;">
            <div class="dash-section-label">Relationships</div>
            <div style="display:flex;flex-direction:column;gap:6px;margin-top:8px;">
                @forelse ($relationships as $rel)
                    @php
                        $other = $rel->staffA?->id === $me->id ? $rel->staffB : $rel->staffA;
                        $tone = $rel->score >= 10 ? 'var(--sky-dark)' : ($rel->score >= 0 ? 'var(--gold-dust)' : 'var(--coral-dark)');
                    @endphp
                    <div style="padding:8px;border-radius:8px;background:var(--pin-white);border:{{ $rel->score >= 10 ? '2px solid var(--sky-dark)' : ($rel->score >= 0 ? '2px solid var(--gold)' : '2px solid var(--coral)') }};">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-family:var(--font-sub);font-size:0.65rem;color:var(--navy);">{{ $other?->user?->name ?? 'Unknown' }}</span>
                            <span style="font-family:var(--font-mono);font-size:0.65rem;font-weight:700;color:{{ $tone }};">{{ $rel->score }}</span>
                        </div>
                        @if ($rel->score >= 10)
                            <span class="pin standing" style="color:var(--sky-dark);font-size:0.75rem;" title="Good">&#9679;</span>
                        @elseif ($rel->score >= 0)
                            <span class="pin standing" style="color:var(--gold);font-size:0.75rem;" title="Neutral">&#9679;</span>
                        @else
                            <span class="pin knocked" style="color:var(--coral-dark);font-size:0.75rem;" title="Bad">&#9679;</span>
                        @endif
                    </div>
                @empty
                    <div style="padding:8px;border-radius:8px;background:var(--pin-white);border:1px solid var(--fog);">
                        <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">No relationships yet.</div>
                    </div>
                @endforelse
                <div style="margin-top:auto;padding-top:8px;border-top:2px solid var(--fog);text-align:center;">
                    <div class="ball-avatar caretaker" style="width:48px;height:48px;border-radius:50%;background:var(--navy);color:var(--pin-white);display:inline-flex;align-items:center;justify-content:center;font-family:var(--font-header);font-size:1.1rem;font-weight:700;">CK</div>
                    <div style="font-family:var(--font-sub);font-size:0.65rem;color:var(--navy);margin-top:4px;font-weight:700;">Caretaker</div>
                </div>
            </div>
        </div>
        <div style="padding:1.25rem;overflow:hidden;">
            <div class="dash-section-label">Break Room Chatter</div>
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-top:8px;margin-bottom:1.25rem;">
                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;padding:12px;">
                    <div style="font-family:var(--font-sub);font-size:0.72rem;color:var(--navy);font-weight:700;margin-bottom:8px;">{{ $me->user->name }} <span style="color:var(--slate);font-family:var(--font-mono);font-size:0.55rem;">(you)</span></div>
                    @include('sim.partials.dialogue', ['bubbles' => $myBubbles])
                </div>
                @foreach($coworkerBubbles as $coworker)
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;padding:12px;">
                        <div style="font-family:var(--font-sub);font-size:0.72rem;color:var(--navy);font-weight:700;margin-bottom:8px;">{{ $coworker['name'] }}</div>
                        @include('sim.partials.dialogue', ['bubbles' => $coworker['bubbles']])
                    </div>
                @endforeach
            </div>

            <div class="dash-section-label">My Confrontations</div>
            <div style="display:flex;flex-direction:column;gap:8px;margin-top:8px;">
                @forelse ($confrontations as $confrontation)
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span class="badge-role" style="background:var(--navy);color:var(--pin-white);font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;">{{ $confrontation->incident_type }}</span>
                            <span style="font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:{{ $confrontation->manager_verdict ? 'var(--mist)' : 'var(--gold-light)' }};color:var(--navy);border:1px solid var(--navy);">{{ $confrontation->manager_verdict ?? 'pending verdict' }}</span>
                            @if ($confrontation->manager_verdict)
                                <span class="pin standing" style="color:var(--sky-dark);font-size:0.75rem;" title="Resolved">&#9679;</span>
                            @else
                                <span class="pin knocked" style="color:var(--gold);font-size:0.75rem;" title="Pending">&#9679;</span>
                            @endif
                        </div>
                        <div style="font-family:var(--font-body);font-size:0.7rem;margin-top:8px;background:var(--pin-white);border-radius:8px;padding:8px 10px;border:1px solid var(--fog);">
                            {{ $confrontation->reporter->user->name }} &#8594; {{ $confrontation->accused->user->name }}
                            @if ($confrontation->incident_description)
                                <div style="margin-top:4px;color:var(--slate);font-size:0.65rem;">{{ $confrontation->incident_description }}</div>
                            @endif
                        </div>
                        @if ($confrontation->staff_response)
                            <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--sky-dark);margin-top:6px;">Accused response: {{ $confrontation->staff_response }}</div>
                        @endif
                    </div>
                @empty
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;text-align:center;">
                        <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">You have not been part of any confrontations.</span>
                    </div>
                @endforelse
            </div>
        </div>
        <div style="background:var(--sky-light);border-left:3px solid var(--navy);padding:1rem;display:flex;flex-direction:column;">
            <div class="dash-section-label">Quick Actions</div>
            <div style="display:flex;flex-direction:column;gap:8px;margin-top:8px;">
                <a href="{{ route('caretaker.dashboard') }}" class="btn-lane secondary" style="display:block;text-align:center;text-decoration:none;font-size:0.65rem;padding:8px 12px;">Dashboard</a>
                <a href="{{ route('caretaker.shifts.index') }}" class="btn-lane secondary" style="display:block;text-align:center;text-decoration:none;font-size:0.65rem;padding:8px 12px;">Shifts</a>
                <a href="{{ route('caretaker.inventory.index') }}" class="btn-lane secondary" style="display:block;text-align:center;text-decoration:none;font-size:0.65rem;padding:8px 12px;">Inventory</a>
            </div>
            <div class="dash-section-label" style="margin-top:16px;">Summary</div>
            <div class="dash-stat" style="margin-top:8px;">
                <span class="dash-stat-num">{{ $relationships->count() }}</span>
                <span class="dash-stat-label">Relationships</span>
            </div>
            <div class="dash-stat" style="margin-top:6px;">
                <span class="dash-stat-num" style="color:var(--gold);">{{ $confrontations->count() }}</span>
                <span class="dash-stat-label">Confrontations</span>
            </div>
            <div style="margin-top:16px;padding-top:8px;border-top:2px solid var(--fog);">
                <div class="dash-section-label">Gossip Corner</div>
                <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);margin-top:4px;line-height:1.5;">
                    Vent about management. It relieves stress… but someone might be listening.
                </div>
                <form method="POST" action="{{ route('caretaker.crew.vent') }}" style="margin-top:8px;">
                    @csrf
                    <button type="submit" class="btn-lane secondary" style="font-size:0.6rem;padding:6px 12px;width:100%;">Vent &amp; Trash-Talk</button>
                </form>
            </div>
        </div>
    </div>

    <x-toast />

    @include('sim.partials.responsive')
</x-app-layout>
