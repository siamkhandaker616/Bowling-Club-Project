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
                            <div class="con-actions" data-con-id="{{ $confrontation->id }}">
                                <button type="button" class="btn-lane primary" style="font-size:0.55rem;padding:5px 12px;" data-interrogate="{{ $confrontation->id }}" data-name="{{ $confrontation->accused->user->name }}">Interrogate</button>
                                <button type="button" class="btn-lane secondary con-auto" style="font-size:0.55rem;padding:5px 12px;" data-url="{{ route('manager.confrontations.respond', $confrontation) }}">Auto-Investigate</button>
                            </div>
                            <div class="con-result" data-con-id="{{ $confrontation->id }}" style="display:none;margin-top:8px;"></div>
                        @elseif (! $confrontation->manager_verdict)
                            <div class="con-verdict-wrap" data-con-id="{{ $confrontation->id }}">
                                <form class="con-verdict-form" data-url="{{ route('manager.confrontations.verdict', $confrontation) }}" style="display:flex;gap:8px;margin-top:10px;align-items:center;flex-wrap:wrap;">
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
                            </div>
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
                <div class="int-typing" id="intTyping">
                    <div class="int-typing-av" id="intTypingAv"></div>
                    <div class="int-typing-text"><span id="intTypingName"></span> <span class="int-typing-dots"><span></span><span></span><span></span></span></div>
                </div>
                <div class="int-chips" id="intChips"></div>
                <form id="intConcludeForm" style="margin-top:12px;text-align:right;">
                    @csrf
                    <button type="button" id="intConcludeBtn" class="btn-lane primary" style="font-size:0.6rem;padding:6px 16px;">Conclude Investigation</button>
                </form>
            </div>
        </div>
    </div>

    <style>
        .int-room{display:flex;flex-direction:column;gap:8px;max-height:300px;overflow-y:auto;padding:4px;}
        .int-row{display:flex;gap:8px;align-items:flex-start;}
        .int-row.mine{flex-direction:row-reverse;}
        .int-av{flex-shrink:0;width:30px;height:30px;border-radius:50%;background:var(--navy);color:var(--gold-light);display:flex;align-items:center;justify-content:center;font-family:var(--font-header);font-size:.6rem;font-weight:700;border:2px solid var(--navy);}
        .int-av.mine{background:var(--coral);color:var(--pin-white);border-color:var(--coral-dark);}
        .int-bub{position:relative;max-width:86%;background:var(--pin-white);border:2px solid var(--navy);border-radius:12px;padding:.5rem .7rem;font-size:.74rem;color:var(--navy);line-height:1.5;box-shadow:var(--hard);}
        .int-bub.mine{background:var(--coral);color:var(--pin-white);border-color:var(--coral-dark);}
        .int-bub.exclam{border-color:var(--coral);border-width:3px;background:var(--mist);}
        .int-bub.thought{border-style:dashed;background:var(--cloud);color:var(--slate);font-style:italic;}
        .int-bub.question{background:var(--sky-dark);border-color:var(--navy);}
        .int-bub .int-name{font-family:var(--font-sub);font-size:.56rem;text-transform:uppercase;letter-spacing:1px;color:var(--gold-dust);display:block;margin-bottom:.15rem;}
        .int-bub.mine .int-name{color:var(--gold-light);}
        .int-chips{display:flex;gap:6px;flex-wrap:wrap;margin-top:10px;}
        .int-chip{border:2px solid var(--navy);border-radius:40px;background:var(--cloud);font-family:var(--font-sub);font-size:.6rem;padding:.35rem .7rem;cursor:pointer;box-shadow:var(--hard);color:var(--navy);}
        .int-chip:hover{transform:translate(-1px,-1px);box-shadow:3px 3px 0 var(--navy);}
        .int-chip:disabled{opacity:.45;cursor:default;transform:none;box-shadow:none;}
        .int-typing{display:none;align-items:center;gap:8px;padding:4px 0;}
        .int-typing.on{display:flex;}
        .int-typing-av{flex-shrink:0;width:30px;height:30px;border-radius:50%;background:var(--navy);color:var(--gold-light);display:flex;align-items:center;justify-content:center;font-family:var(--font-header);font-size:.6rem;font-weight:700;border:2px solid var(--navy);}
        .int-typing-text{font-family:var(--font-sub);font-size:.62rem;color:var(--slate);font-style:italic;}
        .int-typing-dots{display:inline-flex;gap:3px;margin-left:4px;}
        .int-typing-dots span{width:5px;height:5px;border-radius:50%;background:var(--slate);animation:typingBounce .8s infinite ease-in-out;}
        .int-typing-dots span:nth-child(2){animation-delay:.15s;}
        .int-typing-dots span:nth-child(3){animation-delay:.3s;}
    </style>

    <script>
    (function () {
        var modal = document.getElementById('interviewModal');
        var room = document.getElementById('intRoom');
        var chips = document.getElementById('intChips');
        var who = document.getElementById('intWho');
        var intTyping = document.getElementById('intTyping');
        var intTypingAv = document.getElementById('intTypingAv');
        var intTypingName = document.getElementById('intTypingName');
        var csrf = @json(csrf_token());
        @php($managerStaff = auth()->user()->staff)
        var myInitials = @json($managerStaff ? app(\App\Services\Simulation\InterrogationEngine::class)->initials($managerStaff) : '??');
        var active = 0;
        var accusedData = null;
        var urls = {
            interview: @json(route('manager.confrontations.interview', ['confrontation' => '__ID__'])),
            interrogate: @json(route('manager.confrontations.interrogate', ['confrontation' => '__ID__'])),
            conclude: @json(route('manager.confrontations.conclude', ['confrontation' => '__ID__'])),
            respond: @json(route('manager.confrontations.respond', ['confrontation' => '__ID__'])),
            verdict: @json(route('manager.confrontations.verdict', ['confrontation' => '__ID__'])),
        };

        function urlFor(name) { return urls[name].replace('__ID__', active); }

        function appendMessage(m) {
            var row = document.createElement('div');
            row.className = 'int-row' + (m.mine ? ' mine' : '');
            var av = document.createElement('div');
            av.className = 'int-av' + (m.mine ? ' mine' : '');
            av.textContent = m.initials;
            var bwrap = document.createElement('div');
            var bubble = document.createElement('div');
            var type = m.bubble_type === 'exclamation' ? 'exclam' : (m.bubble_type || 'speech');
            bubble.className = 'int-bub ' + type + (m.mine ? ' mine' : '');
            var nm = document.createElement('span');
            nm.className = 'int-name';
            nm.textContent = m.mine ? 'You' : (m.name || 'Crew').toUpperCase();
            bubble.appendChild(nm);
            bubble.appendChild(document.createTextNode(m.body || ''));
            bwrap.appendChild(bubble);
            if (m.mine) { row.appendChild(bwrap); row.appendChild(av); }
            else { row.appendChild(av); row.appendChild(bwrap); }
            room.appendChild(row);
            room.scrollTop = room.scrollHeight;
        }

        function renderChips(list) {
            chips.innerHTML = '';
            list.forEach(function (c) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'int-chip';

                if (c.action === 'conclude') {
                    btn.textContent = c.label;
                    btn.addEventListener('click', function () {
                        document.getElementById('intConcludeBtn').click();
                    });
                } else {
                    var key = c.key || c.action;
                    btn.textContent = c.label;
                    btn.addEventListener('click', function () { ask(key, c.label); });
                }

                chips.appendChild(btn);
            });
        }

        function showTyping(name, initials) {
            intTypingAv.textContent = initials || '';
            intTypingName.textContent = name + ' is responding';
            intTyping.classList.add('on');
            room.scrollTop = room.scrollHeight;
        }

        function hideTyping() {
            intTyping.classList.remove('on');
        }

        function ask(key, label) {
            chips.innerHTML = '';

            if (label) {
                appendMessage({
                    mine: true,
                    initials: myInitials,
                    name: 'You',
                    bubble_type: 'speech',
                    body: label
                });
            }

            if (accusedData) {
                showTyping(accusedData.name, accusedData.initials);
            }

            var minDelay = 1200 + Math.floor(Math.random() * 2000);

            var fetchPromise = fetch(urlFor('interrogate'), {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ key: key })
            })
                .then(function (r) { if (!r.ok) throw 0; return r.json(); });

            Promise.all([fetchPromise, new Promise(function (res) { setTimeout(res, minDelay); })])
                .then(function (results) {
                    var data = results[0];
                    hideTyping();
                    appendMessage(data.reply);
                    renderChips(data.chips);
                })
                .catch(function () { hideTyping(); });
        }

        function openInterview(id, name) {
            active = id;
            who.textContent = name;
            room.innerHTML = '';
            chips.innerHTML = '';
            hideTyping();
            modal.classList.add('open');
            var cBtn = document.getElementById('intConcludeBtn');
            if (cBtn) { cBtn.disabled = false; cBtn.textContent = 'Conclude Investigation'; }
            fetch(urlFor('interview'), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { if (!r.ok) throw 0; return r.json(); })
                .then(function (data) {
                    accusedData = data.accused || null;
                    if (accusedData) {
                        showTyping(accusedData.name, accusedData.initials);
                    }
                    var delay = 800 + Math.floor(Math.random() * 1500);
                    setTimeout(function () {
                        hideTyping();
                        data.messages.forEach(function (m) { appendMessage(m); });
                        renderChips(data.chips);
                    }, delay);
                })
                .catch(function () { closeInterview(); });
        }

        function closeInterview() {
            modal.classList.remove('open');
            active = 0;
        }

        function postConfrontation(url, data, successCb) {
            fetch(url, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify(data || {})
            })
            .then(function (r) { if (!r.ok) throw 0; return r.json(); })
            .then(function (d) { if (successCb) successCb(d); })
            .catch(function () {});
        }

        document.getElementById('intConcludeBtn').addEventListener('click', function () {
            if (!active) return;
            var btn = this;
            btn.disabled = true;
            btn.textContent = 'Concluding…';
            postConfrontation(urlFor('conclude'), {}, function () {
                closeInterview();
            });
        });

        function showConResult(conId, html) {
            var actions = document.querySelector('.con-actions[data-con-id="' + conId + '"]');
            var result = document.querySelector('.con-result[data-con-id="' + conId + '"]');
            if (actions) actions.style.display = 'none';
            if (result) { result.innerHTML = html; result.style.display = 'block'; }
        }

        function showConVerdict(conId, html) {
            var wrap = document.querySelector('.con-verdict-wrap[data-con-id="' + conId + '"]');
            if (wrap) { wrap.innerHTML = html; }
        }

        document.querySelectorAll('.con-auto').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var url = btn.getAttribute('data-url');
                var card = btn.closest('.con-card');
                var conId = card ? card.querySelector('.con-actions')?.getAttribute('data-con-id') : null;
                btn.disabled = true;
                btn.textContent = 'Investigating…';
                postConfrontation(url, {}, function (data) {
                    var resultHtml = '<div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--sky-dark);margin-top:8px;">' + (data.investigation_result || 'Investigation complete.') + '</div>';
                    resultHtml += '<div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);margin-top:4px;">Accused responded: ' + (data.response_text || data.staff_response) + '</div>';
                    if (conId) {
                        showConResult(conId, resultHtml);
                    }
                });
            });
        });

        document.querySelectorAll('.con-verdict-form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var url = form.getAttribute('data-url');
                var card = form.closest('.con-card');
                var conId = card ? card.querySelector('.con-verdict-wrap')?.getAttribute('data-con-id') : null;
                var fd = new FormData(form);
                var data = { verdict: fd.get('verdict') };
                if (fd.get('penalty_amount')) data.penalty_amount = fd.get('penalty_amount');
                postConfrontation(url, data, function (resp) {
                    if (conId) {
                        showConVerdict(conId, '<div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--ok);margin-top:8px;">Verdict applied: ' + resp.manager_verdict + '</div><div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);margin-top:4px;">' + (resp.investigation_result || '') + '</div>');
                    }
                });
            });
        });

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
