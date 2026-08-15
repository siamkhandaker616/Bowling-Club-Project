<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">{{ $staff->user->name }}</h2>
            <div style="display:flex;align-items:center;gap:1rem;">
                <a href="{{ route('manager.staff.edit', $staff) }}" class="btn-lane secondary" style="font-size:0.6rem;padding:5px 14px;">Edit</a>
                <form method="POST" action="{{ route('manager.staff.destroy', $staff) }}" onsubmit="return confirm('Fire {{ $staff->user->name }}?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-lane danger" style="font-size:0.6rem;padding:5px 14px;">Fire</button>
                </form>
                <span class="badge-role manager">Manager</span>
            </div>
        </div>
    </x-slot>

    <style>
        .sim-happy{height:8px;background:var(--fog);border-radius:4px;overflow:hidden;}
        .sim-happy > div{height:100%;border-radius:4px;}
    </style>

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div style="padding:1.25rem;overflow:hidden;">

            <div style="display:grid;grid-template-columns:260px 1fr;gap:1rem;">

            <div style="display:flex;flex-direction:column;gap:12px;">
                <div style="text-align:center;background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;">
                    <div class="ball-avatar ball-md ball-{{ $staff->role === 'club_manager' ? 'navy' : ($staff->role === 'steward' ? 'sky' : 'coral') }}" style="margin:0 auto;width:56px;height:56px;font-size:1rem;">
                        <div class="ball-holes"><span></span><span></span><span></span></div>
                        <span class="ball-initials">{{ strtoupper(substr($staff->user->name, 0, 1)) }}{{ strtoupper(substr(str_replace(' ', '', $staff->user->name), -1)) }}</span>
                    </div>
                    <div style="font-family:var(--font-sub);font-size:0.8rem;margin-top:8px;">{{ $staff->user->name }}</div>
                    <span class="badge-role {{ $staff->role === 'club_manager' ? 'manager' : ($staff->role === 'steward' ? 'steward' : 'caretaker') }}" style="font-size:0.5rem;margin-top:4px;">{{ $staff->role }}</span>
                    <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);margin-top:6px;">Hired {{ $staff->hire_date?->format('M j, Y') ?? '—' }}</div>
                    <div style="font-family:var(--font-mono);font-size:0.55rem;color:{{ $staff->is_active ? 'var(--sky-dark)' : 'var(--coral-dark)' }};margin-top:2px;">{{ $staff->is_active ? '● Active' : '○ Inactive' }}</div>
                </div>

                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;">
                    <div class="dash-section-label">Personality</div>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                        @forelse ($staff->personalities as $p)
                            <span style="font-family:var(--font-mono);font-size:0.6rem;padding:3px 10px;background:var(--gold-light);border:2px solid var(--gold);border-radius:50px;color:var(--gold-dust);">{{ $p->name }}</span>
                        @empty
                            <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">None assigned</span>
                        @endforelse
                    </div>
                </div>

                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;">
                    <div class="dash-section-label">Relationships</div>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        @forelse ($relationships as $rel)
                            @php
                                $other = $rel->staff_a_id === $staff->id ? $rel->staffB : $rel->staffA;
                                $color = match ($rel->level) { 'trusted' => 'var(--sky-dark)', 'friendly' => 'var(--gold)', 'hostile' => 'var(--coral)', default => 'var(--fog)' };
                            @endphp
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 8px;background:var(--pin-white);border-radius:8px;border-left:3px solid {{ $color }};">
                                <span style="font-family:var(--font-sub);font-size:0.62rem;">{{ $other->user->name }}</span>
                                <span style="font-family:var(--font-mono);font-size:0.55rem;color:{{ $color }};">{{ $rel->level }} ({{ $rel->score }})</span>
                            </div>
                        @empty
                            <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">No recorded relationships.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:12px;">

                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;" style="text-align:center;">
                        <div style="font-family:var(--font-mono);font-size:1rem;font-weight:700;color:{{ $staff->happiness < 50 ? 'var(--coral)' : ($staff->happiness < 70 ? 'var(--gold-dust)' : 'var(--sky-dark)') }};">{{ $staff->happiness }}</div>
                        <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">HAPPINESS</div>
                        <div class="sim-happy" style="margin-top:6px;"><div style="width:{{ $staff->happiness }}%;background:{{ $staff->happiness < 50 ? 'var(--coral)' : ($staff->happiness < 70 ? 'var(--gold)' : 'var(--sky-dark)') }};"></div></div>
                    </div>
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;" style="text-align:center;">
                        <div style="font-family:var(--font-mono);font-size:1rem;font-weight:700;color:var(--navy);">{{ $staff->performance_score }}</div>
                        <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">PERFORMANCE</div>
                    </div>
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;" style="text-align:center;">
                        <div style="font-family:var(--font-mono);font-size:1rem;font-weight:700;color:var(--navy);">{{ $staff->honesty_score }}</div>
                        <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">HONESTY</div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;">
                        <div class="dash-section-label">Award Bonus</div>
                        <form method="POST" action="{{ route('manager.staff.bonus', $staff) }}" style="display:grid;gap:8px;">
                            @csrf
                            <select name="type" class="fold-select" style="font-family:var(--font-body);font-size:0.8rem;padding:6px 10px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                                <option value="cash">Cash</option>
                                <option value="time_off">Time Off</option>
                                <option value="recognition">Recognition</option>
                            </select>
                            <input name="amount_or_hours" type="number" step="0.01" min="0" placeholder="$ or hours" required data-stepper="edit" style="font-family:var(--font-body);font-size:0.8rem;padding:6px 10px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                            <input name="reason" type="text" placeholder="Reason" style="font-family:var(--font-body);font-size:0.8rem;padding:6px 10px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                            <button type="submit" class="btn-lane primary" style="font-size:0.6rem;padding:5px 12px;">Give Bonus</button>
                        </form>
                    </div>

                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;">
                        <div class="dash-section-label">Issue Penalty</div>
                        <form method="POST" action="{{ route('manager.staff.penalty', $staff) }}" style="display:grid;gap:8px;">
                            @csrf
                            <select name="type" class="fold-select" style="font-family:var(--font-body);font-size:0.8rem;padding:6px 10px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                                <option value="pay_dock">Pay Dock</option>
                                <option value="extra_hours">Extra Hours</option>
                                <option value="written_warning">Written Warning</option>
                            </select>
                            <input name="amount_or_hours" type="number" step="0.01" min="0" placeholder="$ or hours" required data-stepper="edit" style="font-family:var(--font-body);font-size:0.8rem;padding:6px 10px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                            <input name="reason" type="text" placeholder="Reason" style="font-family:var(--font-body);font-size:0.8rem;padding:6px 10px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                            <button type="submit" class="btn-lane danger" style="font-size:0.6rem;padding:5px 12px;">Issue Penalty</button>
                        </form>
                    </div>
                </div>

                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;">
                    <div class="dash-section-label">Bonus & Penalty History</div>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        @forelse ($staff->bonuses as $b)
                            <div style="display:flex;justify-content:space-between;padding:6px 8px;background:var(--sky);border-radius:8px;border-left:3px solid var(--sky-dark);">
                                <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--sky-dark);">BONUS · {{ $b->type }} · {{ $b->amount_or_hours }}</span>
                                <span style="font-family:var(--font-body);font-size:0.65rem;color:var(--slate);">{{ $b->reason }}</span>
                            </div>
                        @empty
                            <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">No bonuses yet.</span>
                        @endforelse
                        @forelse ($staff->penalties as $p)
                            <div style="display:flex;justify-content:space-between;padding:6px 8px;background:var(--coral-light);border-radius:8px;border-left:3px solid var(--coral);">
                                <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--coral-dark);">PENALTY · {{ $p->type }} · {{ $p->amount_or_hours }}</span>
                                <span style="font-family:var(--font-body);font-size:0.65rem;color:var(--slate);">{{ $p->reason }}</span>
                            </div>
                        @empty
                            <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">No penalties yet.</span>
                        @endforelse
                        @if ($staff->bonuses->isEmpty() && $staff->penalties->isEmpty())
                            <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">No history yet.</span>
                        @endif
                    </div>
                </div>

                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;">
                    <div class="dash-section-label">Event Timeline</div>
                    <div style="display:flex;flex-direction:column;gap:6px;font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">
                        @forelse ($staff->staffEvents->sortByDesc('created_at')->take(12) as $event)
                            <div style="display:flex;gap:8px;align-items:center;">
                                <span style="color:{{ $event->happiness_change > 0 ? 'var(--sky-dark)' : ($event->happiness_change < 0 ? 'var(--coral)' : 'var(--fog)') }};">{{ $event->happiness_change > 0 ? '+' : '' }}{{ $event->happiness_change }}</span>
                                <span>{{ $event->event_type }} — {{ $event->description }}</span>
                            </div>
                        @empty
                            <span>No recorded events.</span>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>

    <x-toast />

    @include('sim.partials.fold-controls')
    @include('sim.partials.responsive')
</x-app-layout>
