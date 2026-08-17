<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Confrontations</h2>

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
                <form method="POST" action="{{ route('manager.confrontations.store') }}" class="con-form gutter-form">
                    @csrf
                    <div class="select-wrap">
                        <select name="reporter_staff_id" class="input select">
                            <option value="" disabled selected>Reporter…</option>
                            @foreach ($activeStaff as $s)
                                <option value="{{ $s->id }}">{{ $s->user->name }}</option>
                            @endforeach
                        </select>
                        <span class="select-arrow">&#9662;</span>
                    </div>
                    <div class="select-wrap">
                        <select name="accused_staff_id" class="input select">
                            <option value="" disabled selected>Accused…</option>
                            @foreach ($activeStaff as $s)
                                <option value="{{ $s->id }}">{{ $s->user->name }}</option>
                            @endforeach
                        </select>
                        <span class="select-arrow">&#9662;</span>
                    </div>
                    <div class="select-wrap">
                        <select name="incident_type" class="input select">
                            <option value="theft">Theft</option>
                            <option value="sabotage">Sabotage</option>
                            <option value="harassment">Harassment</option>
                            <option value="negligence">Negligence</option>
                            <option value="other">Other</option>
                        </select>
                        <span class="select-arrow">&#9662;</span>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <input name="incident_description" type="text" placeholder="What happened…" class="input" style="flex:1;">
                        <label class="pin-check" style="font-size:0.55rem;margin:0;">
                            <input type="checkbox" name="db_verified" value="1"><span class="pin-box"></span> Backed by records
                        </label>
                        <button type="submit" class="btn-lane primary" style="font-size:0.55rem;padding:5px 12px;">Log</button>
                    </div>
                    <div class="lane-stage">
                        <div class="pin-rack">
                            <div class="pin-row"><span class="pin"></span><span class="pin"></span><span class="pin"></span><span class="pin"></span></div>
                            <div class="pin-row"><span class="pin"></span><span class="pin"></span><span class="pin"></span></div>
                            <div class="pin-row"><span class="pin"></span><span class="pin"></span></div>
                            <div class="pin-row"><span class="pin"></span></div>
                        </div>
                        <span class="ball-dot"></span>
                    </div>
                </form>
            </div>

            <div style="display:flex;flex-direction:column;gap:10px;">
                @forelse ($confrontations as $confrontation)
                    <div class="con-card">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <div style="display:flex;gap:8px;align-items:center;">
                                <span class="con-badge" style="background:var(--navy);color:var(--pin-white);">{{ \App\Helpers\Label::incidentType($confrontation->incident_type) }}</span>
                                @if ($confrontation->db_verified)
                                    <span class="con-badge" style="background:var(--sky);color:var(--sky-dark);border:1px solid var(--sky-dark);">VERIFIED</span>
                                @endif
                                <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">{{ $confrontation->date }}</span>
                            </div>
                            <span class="con-badge" style="background:{{ $confrontation->manager_verdict ? 'var(--mist)' : 'var(--gold-light)' }};color:var(--navy);border:1px solid var(--navy);">{{ \App\Helpers\Label::confrontationVerdict($confrontation->manager_verdict ?? '') }}</span>
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
                            <div style="display:flex;gap:8px;margin-top:10px;align-items:center;flex-wrap:wrap;">
                                <button type="button" class="btn-lane primary" style="font-size:0.55rem;padding:5px 12px;" data-interrogate="{{ $confrontation->id }}" data-name="{{ $confrontation->accused->user->name }}">Interrogate</button>
                                <form method="POST" action="{{ route('manager.confrontations.respond', $confrontation) }}">
                                    @csrf
                                    <button type="submit" formnovalidate class="btn-lane secondary" style="font-size:0.55rem;padding:5px 12px;">Auto-Investigate</button>
                                </form>
                            </div>
                        @elseif (! $confrontation->manager_verdict)
                            <form method="POST" action="{{ route('manager.confrontations.verdict', $confrontation) }}" class="gutter-form" style="display:flex;gap:8px;margin-top:10px;align-items:center;flex-wrap:wrap;">
                                @csrf
                                <div class="select-wrap">
                                    <select name="verdict" class="input select">
                                        <option value="upheld">Upheld</option>
                                        <option value="dismissed">Dismissed</option>
                                        <option value="penalized">Penalized</option>
                                        <option value="reporter_penalized">Clear accused — penalize the reporter</option>
                                    </select>
                                    <span class="select-arrow">&#9662;</span>
                                </div>
                                <div class="gutter-field">
                                    <input name="penalty_amount" type="number" step="0.01" min="0" placeholder="Penalty $" class="input" data-stepper="edit" style="width:120px;">
                                    <div class="gutter-err">Enter a valid amount</div>
                                    <div class="gutter-flag">&#10003;</div>
                                </div>
                                <button type="submit" class="btn-lane primary" style="font-size:0.55rem;padding:5px 12px;">Apply Verdict</button>
                                <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">Accused responded: {{ $confrontation->response_text ?? $confrontation->staff_response }}</span>
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

    <div class="modal-back" id="interviewModal">
        <div class="modal">
            <div class="modal-top">Interrogate <span id="intWho" style="font-family:var(--font-sub);"></span> <button type="button" onclick="closeInterview()">&times;</button></div>
            <div class="modal-body">
                <div class="int-room" id="intRoom"></div>
                <div class="int-chips" id="intChips"></div>
                <form method="POST" action="" id="intConcludeForm" style="margin-top:12px;text-align:right;">
                    @csrf
                    <button type="submit" class="btn-lane primary" style="font-size:0.6rem;padding:6px 16px;">Conclude Investigation</button>
                </form>
            </div>
        </div>
    </div>

    <style>
        .int-room{display:flex;flex-direction:column;gap:8px;max-height:300px;overflow-y:auto;padding:4px;}
        .int-row{display:flex;gap:8px;align-items:flex-start;}
        .int-av{flex-shrink:0;width:30px;height:30px;border-radius:50%;background:var(--navy);color:var(--gold-light);display:flex;align-items:center;justify-content:center;font-family:var(--font-header);font-size:.6rem;font-weight:700;border:2px solid var(--navy);}
        .int-bub{position:relative;max-width:86%;background:var(--pin-white);border:2px solid var(--navy);border-radius:12px;padding:.5rem .7rem;font-size:.74rem;color:var(--navy);line-height:1.5;box-shadow:var(--hard);}
        .int-bub.exclam{border-color:var(--coral);border-width:3px;background:var(--mist);}
        .int-bub.thought{border-style:dashed;background:var(--cloud);color:var(--slate);font-style:italic;}
        .int-bub.question{background:var(--sky-dark);border-color:var(--navy);}
        .int-bub .int-name{font-family:var(--font-sub);font-size:.56rem;text-transform:uppercase;letter-spacing:1px;color:var(--gold-dust);display:block;margin-bottom:.15rem;}
        .int-chips{display:flex;gap:6px;flex-wrap:wrap;margin-top:10px;}
        .int-chip{border:2px solid var(--navy);border-radius:40px;background:var(--cloud);font-family:var(--font-sub);font-size:.6rem;padding:.35rem .7rem;cursor:pointer;box-shadow:var(--hard);color:var(--navy);}
        .int-chip:hover{transform:translate(-1px,-1px);box-shadow:3px 3px 0 var(--navy);}
        .int-chip:disabled{opacity:.45;cursor:default;transform:none;box-shadow:none;}
    </style>

    <script>
    (function () {
        var modal = document.getElementById('interviewModal');
        var room = document.getElementById('intRoom');
        var chips = document.getElementById('intChips');
        var who = document.getElementById('intWho');
        var concludeForm = document.getElementById('intConcludeForm');
        var csrf = @json(csrf_token());
        var active = 0;
        var asked = {};
        var urls = {
            interview: @json(route('manager.confrontations.interview', ['confrontation' => '__ID__'])),
            interrogate: @json(route('manager.confrontations.interrogate', ['confrontation' => '__ID__'])),
            conclude: @json(route('manager.confrontations.conclude', ['confrontation' => '__ID__'])),
        };

        function urlFor(name) { return urls[name].replace('__ID__', active); }

        function appendMessage(m) {
            var row = document.createElement('div');
            row.className = 'int-row';
            var av = document.createElement('div');
            av.className = 'int-av';
            av.textContent = m.initials;
            var bwrap = document.createElement('div');
            var bubble = document.createElement('div');
            var type = m.bubble_type === 'exclamation' ? 'exclam' : (m.bubble_type || 'speech');
            bubble.className = 'int-bub ' + type;
            var nm = document.createElement('span');
            nm.className = 'int-name';
            nm.textContent = (m.name || 'Crew').toUpperCase();
            bubble.appendChild(nm);
            bubble.appendChild(document.createTextNode(m.body || ''));
            bwrap.appendChild(bubble);
            row.appendChild(av);
            row.appendChild(bwrap);
            room.appendChild(row);
            room.scrollTop = room.scrollHeight;
        }

        function renderChips(list) {
            chips.innerHTML = '';
            list.forEach(function (c) {
                var key = c.key || c.action;
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'int-chip';
                btn.textContent = c.label;
                if (asked[key]) btn.disabled = true;
                btn.addEventListener('click', function () { ask(key); });
                chips.appendChild(btn);
            });
        }

        function ask(key) {
            if (asked[key]) return;
            asked[key] = true;
            fetch(urlFor('interrogate'), {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ key: key })
            })
                .then(function (r) { if (!r.ok) throw 0; return r.json(); })
                .then(function (data) {
                    appendMessage(data.reply);
                    renderChips(data.chips);
                })
                .catch(function () { asked[key] = false; });
        }

        function openInterview(id, name) {
            active = id;
            asked = {};
            who.textContent = name;
            room.innerHTML = '';
            chips.innerHTML = '';
            modal.classList.add('open');
            concludeForm.action = urlFor('conclude');
            fetch(urlFor('interview'), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { if (!r.ok) throw 0; return r.json(); })
                .then(function (data) {
                    data.messages.forEach(function (m) { appendMessage(m); });
                    renderChips(data.chips);
                })
                .catch(function () { closeInterview(); });
        }

        window.closeInterview = function () {
            modal.classList.remove('open');
            active = 0;
        };

        document.querySelectorAll('[data-interrogate]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                openInterview(parseInt(btn.getAttribute('data-interrogate'), 10), btn.getAttribute('data-name'));
            });
        });

        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) window.closeInterview();
            });
        }
    })();
    </script>

    <x-toast />

    @include('sim.partials.fold-controls')
    @include('sim.partials.responsive')
</x-app-layout>
