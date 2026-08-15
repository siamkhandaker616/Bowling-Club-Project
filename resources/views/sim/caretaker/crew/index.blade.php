<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Crew Social</h2>
            <span class="badge-role caretaker">Caretaker</span>
        </div>
    </x-slot>

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div class="sim-crew" style="display:grid;grid-template-columns:1fr 200px;gap:1.2rem;align-items:start;">

            <div style="min-width:0;">

                <div class="sim-tab {{ $tab === 'crew' ? 'on' : '' }}" id="tab-crew">
                    <div class="card">
                        <div class="panel-head">
                            <h3 class="panel-title">The Staff Room</h3>
                            <div style="display:flex;align-items:center;gap:.5rem;">
                                <span class="badge navy">DAY {{ $day }}</span>
                                <form method="POST" action="{{ route('caretaker.crew.vent') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-xs" title="Vent about management… someone might be listening" style="font-size:.56rem;padding:.35rem .7rem;">Vent &#8594;</button>
                                </form>
                            </div>
                        </div>

                        <div class="sim-crew-room" id="sim-room">
                            @forelse ($thread as $message)
                                @php
                                    $staff = $message->staff;
                                    $mine = (int) $message->staff_id === (int) $me->id;
                                    $btype = $message->bubble_type === 'exclamation' ? 'exclam' : $message->bubble_type;
                                @endphp
                                <div class="sim-crew-row {{ $mine ? 'mine' : '' }}" data-mid="{{ $message->id }}">
                                    @if (! $mine)
                                        <div class="sim-crew-av">{{ $engine->initials($staff) }}</div>
                                    @endif
                                    <div class="sim-crew-bwrap">
                                        <div class="bubble {{ $btype }}">
                                            <span class="b-name">{{ $mine ? 'You' : strtoupper($staff->user->name ?? 'Crew') }}</span>{{ $message->body }}
                                        </div>
                                    </div>
                                    @if ($mine)
                                        <div class="sim-crew-av mine">{{ $engine->initials($me) }}</div>
                                    @endif
                                </div>
                            @empty
                                <div style="text-align:center;padding:1.5rem;">
                                    <span style="font-family:var(--font-mono);font-size:.65rem;color:var(--slate);">The staff room is quiet today.</span>
                                </div>
                            @endforelse
                        </div>

                        <div style="border-top:2px dashed var(--fog);margin-top:.9rem;padding-top:.9rem;">
                            <div class="sim-crew-chips" id="sim-group-chips">
                                @foreach ($vibe as $chip)
                                    <button type="button" class="sim-crew-chip" data-body="{{ $chip['label'] }}">{{ $chip['label'] }}</button>
                                @endforeach
                            </div>
                            <form method="POST" action="{{ route('caretaker.crew.send') }}" class="sim-crew-bar" id="sim-group-form">
                                @csrf
                                <input type="text" name="body" id="sim-group-input" class="sim-crew-input" placeholder="Say something to the crew…" maxlength="500" autocomplete="off">
                                <button type="submit" class="btn btn-xs" style="font-size:.58rem;padding:.45rem .9rem;">Send &#8594;</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="sim-tab {{ $tab === 'dm' ? 'on' : '' }}" id="tab-dm">
                    <div class="card">
                        <div class="panel-head"><h3 class="panel-title">Direct Messages</h3><span class="badge sky">{{ $dms->sum('unread') }} NEW</span></div>

                        @if ($open)
                            <div class="sim-dm-split">
                                <div class="sim-dm-side">
                                    @foreach ($dms as $dm)
                                        <a href="{{ route('caretaker.crew.index', ['with' => $dm['staff']->id, 'tab' => 'dm']) }}" class="sim-crew-dmrow {{ $open->id === $dm['staff']->id ? 'on' : '' }}">
                                            <div class="sim-crew-av sm">{{ $engine->initials($dm['staff']) }}</div>
                                            <div class="sim-crew-dmmain">
                                                <div class="sim-crew-dmname">{{ $dm['staff']->user->name ?? 'Crew' }}@if ($dm['staff']->role === 'steward') <span class="sim-crew-dmrole">steward</span>@endif</div>
                                                <div class="sim-crew-dmpreview">{{ $dm['last'] ? (($dm['last_by'] === 'you' ? 'You: ' : '') . $dm['last']) : 'Start a chat…' }}</div>
                                            </div>
                                            @if ($dm['unread'] > 0)
                                                <span class="sim-crew-unread">{{ $dm['unread'] }}</span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>

                                <div class="sim-dm-thread">
                                    <div style="font-family:var(--font-sub);font-size:.7rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin-bottom:.5rem;">
                                        <span class="sim-crew-av sm" style="display:inline-flex;vertical-align:middle;margin-right:.4rem;">{{ $engine->initials($open) }}</span>{{ $open->user->name ?? 'Crew' }}
                                    </div>

                                    <div class="sim-crew-room dm" id="sim-dm-room" data-with="{{ $open->id }}">
                                        @foreach ($dmThread as $message)
                                            @php
                                                $mine = (int) $message->staff_id === (int) $me->id;
                                            @endphp
                                            <div class="sim-crew-row {{ $mine ? 'mine' : '' }}" data-mid="{{ $message->id }}">
                                                @if (! $mine)
                                                    <div class="sim-crew-av sm">{{ $engine->initials($open) }}</div>
                                                @endif
                                                <div class="sim-crew-bwrap">
                                                    <div class="bubble {{ $message->bubble_type === 'exclamation' ? 'exclam' : $message->bubble_type }}">
                                                        <span class="b-name">{{ $mine ? 'You' : strtoupper($open->user->name ?? 'Crew') }}</span>{{ $message->body }}
                                                    </div>
                                                </div>
                                                @if ($mine)
                                                    <div class="sim-crew-av sm mine">{{ $engine->initials($me) }}</div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="sim-crew-chips" id="sim-dm-chips">
                                        @foreach ($dmChips as $chip)
                                            @if ($chip['action'] === 'snitch')
                                                <form method="POST" action="{{ route('caretaker.crew.reply', ['message' => $chip['message_id'], 'with' => $open->id]) }}">
                                                    @csrf
                                                    <input type="hidden" name="action" value="snitch">
                                                    <button type="submit" class="sim-crew-chip snitch">Snitch &#128037;</button>
                                                </form>
                                            @else
                                                <button type="button" class="sim-crew-chip" data-body="{{ $chip['label'] }}" data-to="{{ $open->id }}">{{ $chip['label'] }}</button>
                                            @endif
                                        @endforeach
                                    </div>

                                    <form method="POST" action="{{ route('caretaker.crew.send') }}" class="sim-crew-bar" id="sim-dm-form">
                                        @csrf
                                        <input type="hidden" name="to" value="{{ $open->id }}">
                                        <input type="text" name="body" id="sim-dm-input" class="sim-crew-input" placeholder="Reply to {{ $open->user->name ?? 'them' }}…" maxlength="500" autocomplete="off">
                                        <button type="submit" class="btn btn-xs" style="font-size:.58rem;padding:.45rem .9rem;">Send &#8594;</button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="sim-dm-side" style="border:none;">
                                @foreach ($dms as $dm)
                                    <a href="{{ route('caretaker.crew.index', ['with' => $dm['staff']->id, 'tab' => 'dm']) }}" class="sim-crew-dmrow">
                                        <div class="sim-crew-av sm">{{ $engine->initials($dm['staff']) }}</div>
                                        <div class="sim-crew-dmmain">
                                            <div class="sim-crew-dmname">{{ $dm['staff']->user->name ?? 'Crew' }}@if ($dm['staff']->role === 'steward') <span class="sim-crew-dmrole">steward</span>@endif</div>
                                            <div class="sim-crew-dmpreview">{{ $dm['last'] ? (($dm['last_by'] === 'you' ? 'You: ' : '') . $dm['last']) : 'Start a chat…' }}</div>
                                        </div>
                                        @if ($dm['unread'] > 0)
                                            <span class="sim-crew-unread">{{ $dm['unread'] }}</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="sim-tab {{ $tab === 'reported' ? 'on' : '' }}" id="tab-reported">
                    <div class="card" style="border-color:var(--coral);border-width:3px;">
                        <div class="panel-head"><h3 class="panel-title">You've Been Reported</h3><span class="badge coral">{{ $accusations->count() }} OPEN</span></div>
                        @forelse ($accusations as $confrontation)
                            <div style="border-top:1px dashed var(--fog);padding-top:.7rem;margin-top:.2rem;">
                                <div style="font-family:var(--font-sub);font-size:.74rem;color:var(--navy);">
                                    <span style="color:var(--slate);">About:</span> {{ $confrontation->incident_type }}
                                    @if ($confrontation->incident_description)
                                        <span style="font-family:var(--font-body);font-size:.68rem;color:var(--slate);display:block;margin-top:.2rem;">{{ $confrontation->incident_description }}</span>
                                    @endif
                                </div>
                                <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.6rem;">
                                    <form method="POST" action="{{ route('caretaker.crew.respond', $confrontation) }}">
                                        @csrf
                                        <input type="hidden" name="response" value="confessed">
                                        <button type="submit" class="btn btn-xs" style="font-size:.56rem;padding:.35rem .75rem">Apologize &#8594;</button>
                                    </form>
                                    <form method="POST" action="{{ route('caretaker.crew.respond', $confrontation) }}">
                                        @csrf
                                        <input type="hidden" name="response" value="innocent">
                                        <button type="submit" class="btn btn-ghost btn-xs" style="font-size:.56rem;padding:.35rem .75rem">Deny</button>
                                    </form>
                                    <form method="POST" action="{{ route('caretaker.crew.respond', $confrontation) }}">
                                        @csrf
                                        <input type="hidden" name="response" value="bs">
                                        <button type="submit" class="btn btn-ghost btn-xs" style="font-size:.56rem;padding:.35rem .75rem">Brush Off</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div style="text-align:center;padding:1.5rem;">
                                <span style="font-family:var(--font-mono);font-size:.65rem;color:var(--slate);">No open reports. Keep it clean and it stays that way.</span>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="sim-tab {{ $tab === 'relationships' ? 'on' : '' }}" id="tab-relationships">
                    <div class="card">
                        <div class="panel-head"><h3 class="panel-title">Relationship Meter</h3><span class="badge sky">TRUST</span></div>
                        @forelse ($crewRelations as $rel)
                            @php
                                $other = $rel['staff'];
                                $segs = match ($rel['level']) {
                                    'trusted' => ['on hot', 'on hot', 'on warm', 'on'],
                                    'friendly' => ['on hot', 'on warm', 'on', ''],
                                    'neutral' => ['on warm', 'on', '', ''],
                                    default => ['on', '', '', ''],
                                };
                            @endphp
                            <div class="crew-row">
                                <span class="cr-name">{{ $other?->user?->name ?? 'Crew' }}@if ($other?->role === 'steward') <span style="font-family:var(--font-mono);font-size:.52rem;color:var(--slate);text-transform:uppercase;">&middot; steward</span>@endif</span>
                                <div class="rel-meter">
                                    <div class="rel-track">
                                        @foreach ($segs as $seg)
                                            <i class="{{ $seg }}"></i>
                                        @endforeach
                                    </div>
                                    <span class="rel-tag {{ $rel['level'] }}">{{ $rel['level'] }}</span>
                                </div>
                                <span style="font-family:var(--font-mono);font-size:.58rem;color:var(--slate);min-width:28px;text-align:right;">{{ $rel['score'] }}</span>
                            </div>
                        @empty
                            <div style="text-align:center;padding:1rem;">
                                <span style="font-family:var(--font-mono);font-size:.65rem;color:var(--slate);">No crew on shift yet.</span>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="sim-tab {{ $tab === 'ledger' ? 'on' : '' }}" id="tab-ledger">
                    <div class="card">
                        <div class="panel-head"><h3 class="panel-title">Snitch Ledger</h3><span class="badge coral">{{ $ledger->count() }} REPORTS</span></div>
                        <p style="font-size:.78rem;color:var(--slate);margin:.3rem 0 .6rem;">The crew remembers. Snitching on a teammate drops your trust with them — the manager gains trust with you. Pick your battles.</p>
                        <div style="display:flex;flex-direction:column;">
                            @forelse ($ledger as $report)
                                <div class="crew-row" style="padding:.5rem .2rem;">
                                    <span class="cr-name" style="min-width:0;">&#8594; {{ $report->accused->user->name ?? 'Coworker' }}</span>
                                    <span class="badge {{ match ($report->status) { 'pending' => 'gold', 'escalated' => 'sky', 'resolved' => 'ok', default => 'coral' } }}" style="font-size:.52rem;">{{ strtoupper($report->status) }}</span>
                                    @if ($report->confrontation && $report->confrontation->manager_verdict)
                                        <span style="font-family:var(--font-mono);font-size:.55rem;color:var(--gold-dust);">verdict: {{ $report->confrontation->manager_verdict }}</span>
                                    @endif
                                </div>
                            @empty
                                <div style="text-align:center;padding:.6rem;">
                                    <span style="font-family:var(--font-mono);font-size:.62rem;color:var(--slate);">Nothing snitched so far. The ledger stays clean.</span>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="sim-tab {{ $tab === 'history' ? 'on' : '' }}" id="tab-history">
                    <div class="card">
                        <div class="panel-head"><h3 class="panel-title">Confrontation History</h3><span class="badge navy">LOG</span></div>
                        <div style="display:flex;flex-direction:column;">
                            @forelse ($confrontations as $confrontation)
                                <div class="crew-row" style="padding:.5rem .2rem;">
                                    <span class="cr-name" style="min-width:0;">{{ $confrontation->reporter->user->name ?? 'Crew' }} &#8594; {{ $confrontation->accused->user->name ?? 'Crew' }}</span>
                                    <span style="font-family:var(--font-mono);font-size:.55rem;color:var(--slate);">{{ $confrontation->created_at->format('M j') }} &middot; {{ $confrontation->incident_type }}</span>
                                    <span class="badge {{ $confrontation->manager_verdict ? 'ok' : 'gold' }}" style="font-size:.52rem;">{{ $confrontation->manager_verdict ?? 'pending' }}</span>
                                </div>
                            @empty
                                <div style="text-align:center;padding:.8rem;">
                                    <span style="font-family:var(--font-mono);font-size:.62rem;color:var(--slate);">No confrontations on record yet.</span>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>

            <aside class="sim-crew-menu card">
                <a href="#crew" class="sim-crew-menu-item on" data-tab="crew">
                    <span>Group Chat</span>
                    <span class="sim-crew-menu-cnt badge navy">&#128172;</span>
                </a>
                <a href="#dm" class="sim-crew-menu-item" data-tab="dm">
                    <span>Direct Messages</span>
                    @if ($dms->sum('unread') > 0)
                        <span class="sim-crew-menu-cnt badge coral">{{ $dms->sum('unread') }}</span>
                    @endif
                </a>
                <a href="#reported" class="sim-crew-menu-item" data-tab="reported">
                    <span>You've Been Reported</span>
                    @if ($accusations->count() > 0)
                        <span class="sim-crew-menu-cnt badge coral">{{ $accusations->count() }}</span>
                    @endif
                </a>
                <a href="#relationships" class="sim-crew-menu-item" data-tab="relationships">
                    <span>Relationship Meter</span>
                </a>
                <a href="#ledger" class="sim-crew-menu-item" data-tab="ledger">
                    <span>Snitch Ledger</span>
                    @if ($ledger->count() > 0)
                        <span class="sim-crew-menu-cnt badge gold">{{ $ledger->count() }}</span>
                    @endif
                </a>
                <a href="#history" class="sim-crew-menu-item" data-tab="history">
                    <span>Confrontation History</span>
                </a>
            </aside>

        </div>
    </div>

    <x-toast />

    @include('sim.partials.responsive')

    <style>
        .sim-tab{display:none}
        .sim-tab.on{display:block}
        .sim-crew-menu{display:flex;flex-direction:column;gap:.4rem;position:sticky;top:1rem;padding:.7rem}
        .sim-crew-menu-item{display:flex;justify-content:space-between;align-items:center;gap:.5rem;padding:.55rem .7rem;border:2px solid var(--navy);border-radius:10px;background:var(--pin-white);font-family:var(--font-sub);font-size:.66rem;color:var(--navy);text-decoration:none;box-shadow:var(--hard);transition:transform .12s ease,box-shadow .12s ease,background .12s ease;letter-spacing:.3px}
        .sim-crew-menu-item:hover{transform:translate(-1px,-1px);box-shadow:3px 3px 0 var(--navy)}
        .sim-crew-menu-item.on{background:var(--navy);color:var(--gold-light);border-color:var(--navy)}
        .sim-crew-menu-item.on .badge{background:var(--gold);color:var(--navy)}
        .sim-crew-menu-cnt{font-size:.5rem;padding:2px 6px}
        .sim-dm-split{display:grid;grid-template-columns:230px 1fr;gap:1rem;min-height:320px}
        .sim-dm-side{display:flex;flex-direction:column;border-right:2px dashed var(--fog);padding-right:.8rem}
        .sim-crew-room{max-height:420px;overflow-y:auto;display:flex;flex-direction:column;gap:.7rem;padding:.2rem .2rem .4rem}
        .sim-crew-room.dm{max-height:280px}
        .sim-crew-row{display:flex;gap:.6rem;align-items:flex-start}
        .sim-crew-row.mine{flex-direction:row-reverse}
        .sim-crew-bwrap{display:flex;flex-direction:column;gap:.35rem;align-items:flex-start;max-width:86%}
        .sim-crew-row.mine .sim-crew-bwrap{align-items:flex-end}
        .sim-crew-av{flex-shrink:0;width:34px;height:34px;border-radius:50%;background:var(--navy);color:var(--gold-light);display:flex;align-items:center;justify-content:center;font-family:var(--font-header);font-size:.66rem;font-weight:700;border:2px solid var(--navy);box-shadow:var(--hard)}
        .sim-crew-av.mine{background:var(--coral);color:var(--pin-white);border-color:var(--coral-dark)}
        .sim-crew-av.sm{width:26px;height:26px;font-size:.54rem}
        .sim-crew .bubble{position:relative;max-width:420px;background:var(--pin-white);border:2px solid var(--navy);border-radius:14px;padding:.6rem .8rem;font-size:.78rem;color:var(--navy);box-shadow:var(--hard);line-height:1.5}
        .sim-crew .bubble::before{content:'';position:absolute;left:-12px;top:16px;border:7px solid transparent;border-right-color:var(--navy)}
        .sim-crew .bubble .b-name{font-family:var(--font-sub);font-size:.6rem;text-transform:uppercase;letter-spacing:1px;color:var(--gold-dust);display:block;margin-bottom:.15rem}
        .sim-crew .bubble.speech{border-color:var(--navy)}
        .sim-crew .bubble.thought{border-style:dashed;background:var(--cloud);color:var(--slate);font-style:italic}
        .sim-crew .bubble.exclam{border-color:var(--coral);border-width:3px;background:var(--mist)}
        .sim-crew .bubble.question{background:var(--sky-dark);border-color:var(--navy)}
        .sim-crew-row.mine .bubble::before{left:auto;right:-12px;border-right-color:transparent;border-left-color:var(--navy)}
        .sim-crew-chips{display:flex;gap:.45rem;flex-wrap:wrap;margin-bottom:.55rem}
        .sim-crew-chip{border:2px solid var(--navy);border-radius:40px;background:var(--cloud);font-family:var(--font-sub);font-size:.62rem;padding:.35rem .75rem;cursor:pointer;box-shadow:var(--hard);color:var(--navy);transition:transform .12s ease,box-shadow .12s ease}
        .sim-crew-chip:hover{transform:translate(-1px,-1px);box-shadow:3px 3px 0 var(--navy)}
        .sim-crew-chip.snitch{border-color:var(--coral-dark);background:var(--coral);color:var(--pin-white)}
        .sim-crew-bar{display:flex;gap:.5rem;align-items:center}
        .sim-crew-input{flex:1;min-width:0;padding:.55rem .8rem;border:2px solid var(--navy);border-radius:var(--radius-sm);font-family:var(--font-body);font-size:.78rem;background:var(--pin-white);color:var(--navy);box-shadow:var(--hard);outline:none}
        .sim-crew-dmrow{display:flex;gap:.6rem;align-items:center;padding:.55rem .4rem;border-bottom:1px dashed var(--fog);text-decoration:none;border-radius:10px}
        .sim-crew-dmrow:hover{background:var(--sky-light)}
        .sim-crew-dmrow.on{background:var(--mist);box-shadow:inset 3px 0 0 var(--navy)}
        .sim-crew-dmmain{flex:1;min-width:0}
        .sim-crew-dmname{font-family:var(--font-sub);font-size:.74rem;color:var(--navy);font-weight:700}
        .sim-crew-dmrole{font-family:var(--font-mono);font-size:.48rem;color:var(--slate);text-transform:uppercase;letter-spacing:1px;margin-left:.3rem;font-weight:400}
        .sim-crew-dmpreview{font-family:var(--font-body);font-size:.64rem;color:var(--slate);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px}
        .sim-crew-unread{flex-shrink:0;min-width:18px;height:18px;border-radius:50%;background:var(--coral);color:var(--pin-white);font-family:var(--font-mono);font-size:.56rem;display:flex;align-items:center;justify-content:center;border:2px solid var(--navy);padding:0 3px}
        .sim-crew .crew-row{display:flex;align-items:center;gap:.5rem;padding:.55rem .2rem;border-bottom:1px dashed var(--fog)}
        .sim-crew .crew-row:last-child{border-bottom:none}
        .sim-crew .cr-name{font-family:var(--font-sub);font-size:.74rem;color:var(--navy);font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex:1;min-width:0}
        .sim-crew .rel-meter{display:flex;align-items:center;gap:.5rem;flex:1;min-width:0}
        .sim-crew .rel-track{flex:1;height:12px;border:2px solid var(--navy);border-radius:6px;background:var(--mist);overflow:hidden;display:flex}
        .sim-crew .rel-track i{flex:1;border-right:2px solid var(--navy)}
        .sim-crew .rel-track i:last-child{border-right:none}
        .sim-crew .rel-track i.on{background:var(--ok)}
        .sim-crew .rel-track i.on.warm{background:var(--gold)}
        .sim-crew .rel-track i.on.hot{background:var(--coral)}
        .sim-crew .rel-tag{font-family:var(--font-mono);font-size:.54rem;text-transform:uppercase;letter-spacing:1px;font-weight:700}
        .sim-crew .rel-tag.hostile{color:var(--coral)}
        .sim-crew .rel-tag.neutral{color:var(--gold-dust)}
        .sim-crew .rel-tag.friendly{color:var(--ok)}
        .sim-crew .rel-tag.trusted{color:var(--ok)}
        @media (max-width:1100px){
            .sim-dm-split{grid-template-columns:1fr}
            .sim-dm-side{border-right:none;border-bottom:2px dashed var(--fog);padding-right:0;padding-bottom:.6rem}
        }
        @media (max-width:900px){
            .sim-crew{grid-template-columns:1fr!important}
            .sim-crew-menu{position:static}
        }
    </style>

    <script>
        (function () {
            var group = document.getElementById('sim-room');
            if (!group) return;

            var groupForm = document.getElementById('sim-group-form');
            var groupInput = document.getElementById('sim-group-input');
            var groupChips = document.getElementById('sim-group-chips');

            var dmRoom = document.getElementById('sim-dm-room');
            var dmForm = document.getElementById('sim-dm-form');
            var dmInput = document.getElementById('sim-dm-input');
            var dmChips = document.getElementById('sim-dm-chips');

            var csrf = @json(csrf_token());
            var groupPollUrl = @json(route('caretaker.crew.poll'));
            var dmPollUrl = @json(route('caretaker.crew.dm'));
            var replyUrl = @json(route('caretaker.crew.reply', ['message' => '__ID__']));

            var lastGroup = {{ $thread->last()?->id ?? 0 }};
            var lastDm = {{ $dmThread->last()?->id ?? 0 }};
            var dmWith = dmRoom ? parseInt(dmRoom.getAttribute('data-with'), 10) : 0;

            var validTabs = ['crew', 'dm', 'reported', 'relationships', 'ledger', 'history'];
            var tab = @json($tab);
            if (validTabs.indexOf(tab) < 0) tab = 'crew';

            function scroll(el) { if (el) el.scrollTop = el.scrollHeight; }

            function activate(name) {
                validTabs.forEach(function (t) {
                    var panel = document.getElementById('tab-' + t);
                    var item = document.querySelector('.sim-crew-menu-item[data-tab="' + t + '"]');
                    if (panel) panel.classList.toggle('on', t === name);
                    if (item) item.classList.toggle('on', t === name);
                });
                tab = name;
                if (name === 'dm' && dmRoom) scroll(dmRoom);
                if (name === 'crew') scroll(group);
            }

            activate(tab);

            document.querySelectorAll('.sim-crew-menu-item').forEach(function (item) {
                item.addEventListener('click', function (e) {
                    e.preventDefault();
                    activate(item.getAttribute('data-tab'));
                    history.replaceState(null, '', '#tab-' + item.getAttribute('data-tab'));
                });
            });

            window.addEventListener('hashchange', function () {
                var name = (location.hash || '').replace('#tab-', '');
                if (validTabs.indexOf(name) >= 0) activate(name);
            });

            function bubbleRow(m) {
                var row = document.createElement('div');
                row.className = 'sim-crew-row' + (m.mine ? ' mine' : '');
                row.setAttribute('data-mid', m.id);

                var av = document.createElement('div');
                av.className = 'sim-crew-av' + (dmRoom ? ' sm' : '') + (m.mine ? ' mine' : '');
                av.textContent = m.initials;

                var bwrap = document.createElement('div');
                bwrap.className = 'sim-crew-bwrap';

                var bubble = document.createElement('div');
                var btype = m.bubble_type === 'exclamation' ? 'exclam' : (m.bubble_type || 'speech');
                bubble.className = 'bubble ' + btype;

                var nm = document.createElement('span');
                nm.className = 'b-name';
                nm.textContent = m.mine ? 'You' : (m.name || 'Crew').toUpperCase();
                bubble.appendChild(nm);
                bubble.appendChild(document.createTextNode(m.body || ''));

                bwrap.appendChild(bubble);

                if (m.mine) { row.appendChild(bwrap); row.appendChild(av); }
                else { row.appendChild(av); row.appendChild(bwrap); }
                return row;
            }

            function renderChips(container, chips, toId) {
                container.innerHTML = '';
                chips.forEach(function (c) {
                    if (c.action === 'snitch') {
                        var form = document.createElement('form');
                        form.method = 'POST';
                        form.action = replyUrl.replace('__ID__', c.message_id) + (toId ? ('?with=' + toId) : '');
                        var t = document.createElement('input');
                        t.type = 'hidden'; t.name = '_token'; t.value = csrf;
                        var a = document.createElement('input');
                        a.type = 'hidden'; a.name = 'action'; a.value = 'snitch';
                        var b = document.createElement('button');
                        b.type = 'submit'; b.className = 'sim-crew-chip snitch';
                        b.textContent = 'Snitch \uD83D\uDC25';
                        form.appendChild(t); form.appendChild(a); form.appendChild(b);
                        container.appendChild(form);
                        return;
                    }
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'sim-crew-chip';
                    btn.textContent = c.label;
                    btn.setAttribute('data-body', c.label);
                    if (toId) btn.setAttribute('data-to', toId);
                    container.appendChild(btn);
                });
            }

            function sendFromChip(btn) {
                var input = btn.getAttribute('data-to') ? dmInput : groupInput;
                var form = btn.getAttribute('data-to') ? dmForm : groupForm;
                if (!input || !form) return;
                input.value = btn.getAttribute('data-body');
                form.submit();
            }

            (groupChips ? Array.prototype.slice.call(groupChips.children) : []).forEach(function (c) {
                c.addEventListener('click', function () { sendFromChip(c); });
            });
            if (dmChips) {
                Array.prototype.slice.call(dmChips.children).forEach(function (c) {
                    if (c.tagName === 'BUTTON') c.addEventListener('click', function () { sendFromChip(c); });
                });
            }

            setInterval(function () {
                fetch(groupPollUrl + '?after=' + lastGroup, { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.messages && data.messages.length) {
                            data.messages.forEach(function (m) {
                                if (m.id <= lastGroup) return;
                                group.appendChild(bubbleRow(m));
                                lastGroup = m.id;
                            });
                        }
                        if (data.chips && groupChips) {
                            renderChips(groupChips, data.chips, null);
                            Array.prototype.slice.call(groupChips.children).forEach(function (c) {
                                c.addEventListener('click', function () { sendFromChip(c); });
                            });
                        }
                        if (tab === 'crew') scroll(group);
                    })
                    .catch(function () {});
            }, 8000);

            if (dmRoom && dmWith) {
                setInterval(function () {
                    fetch(dmPollUrl + '?with=' + dmWith, { headers: { 'Accept': 'application/json' } })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (data.messages && data.messages.length) {
                                data.messages.forEach(function (m) {
                                    if (m.id <= lastDm) return;
                                    dmRoom.appendChild(bubbleRow(m));
                                    lastDm = m.id;
                                });
                            }
                            if (data.chips && dmChips) {
                                renderChips(dmChips, data.chips, dmWith);
                                Array.prototype.slice.call(dmChips.children).forEach(function (c) {
                                    if (c.tagName === 'BUTTON') c.addEventListener('click', function () { sendFromChip(c); });
                                });
                            }
                            if (tab === 'dm') scroll(dmRoom);
                        })
                        .catch(function () {});
                }, 8000);
            }
        })();
    </script>
</x-app-layout>
