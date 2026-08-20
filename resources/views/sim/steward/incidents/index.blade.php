<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Incident Reports</h2>

        </div>
    </x-slot>

    <style>
        .inc-card{background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;}
        .inc-badge{font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;}
        .inc-form{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-top:8px;}
    </style>

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div style="padding:1.25rem;overflow:hidden;">

            <div class="inc-card" style="margin-bottom:1.25rem;">
                <div class="dash-section-label">Log an Incident Report</div>
                <div style="font-family:var(--font-body);font-size:0.66rem;color:var(--slate);margin-bottom:.5rem;">Reports land on the manager's confrontation desk for interrogation and verdict. Accusations can only name caretakers.</div>
                <form id="incForm" class="inc-form gutter-form" style="grid-template-columns:1fr 1fr;">
                    @csrf
                    <div class="select-wrap">
                        <select name="reporter_staff_id" id="incReporter" class="input select">
                            <option value="" disabled selected>Reporter…</option>
                            @foreach ($reporters as $s)
                                <option value="{{ $s->id }}">{{ $s->user->name }} ({{ \App\Helpers\Label::staffRole($s->role) }})</option>
                            @endforeach
                        </select>
                        <span class="select-arrow">&#9662;</span>
                    </div>
                    <div class="select-wrap">
                        <select name="accused_staff_id" id="incAccused" class="input select">
                            <option value="" disabled selected>Accused caretaker…</option>
                            @foreach ($accused as $s)
                                <option value="{{ $s->id }}">{{ $s->user->name }}</option>
                            @endforeach
                        </select>
                        <span class="select-arrow">&#9662;</span>
                    </div>
                    <div class="select-wrap">
                        <select name="incident_type" id="incType" class="input select">
                            <option value="theft">Theft</option>
                            <option value="sabotage">Sabotage</option>
                            <option value="harassment">Harassment</option>
                            <option value="negligence">Negligence</option>
                            <option value="other">Other</option>
                        </select>
                        <span class="select-arrow">&#9662;</span>
                    </div>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <input name="incident_description" id="incDesc" type="text" placeholder="What happened…" class="input" style="flex:1;">
                        <label class="pin-check" style="font-size:0.55rem;margin:0;white-space:nowrap;">
                            <input type="checkbox" name="db_verified" id="incVerified" value="1"><span class="pin-box"></span> Records
                        </label>
                        <button type="submit" class="btn-lane primary" id="incSubmit" style="font-size:0.55rem;padding:5px 12px;">Log</button>
                    </div>
                </form>
            </div>

            <div style="display:flex;flex-direction:column;gap:10px;" id="incList">
                @forelse ($confrontations as $confrontation)
                    <div class="inc-card inc-row">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <div style="display:flex;gap:8px;align-items:center;">
                                <span class="inc-badge" style="background:var(--navy);color:var(--pin-white);">{{ \App\Helpers\Label::incidentType($confrontation->incident_type) }}</span>
                                @if ($confrontation->db_verified)
                                    <span class="inc-badge" style="background:var(--sky);color:var(--sky-dark);border:1px solid var(--sky-dark);">VERIFIED</span>
                                @endif
                                <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">{{ $confrontation->created_at->format('M j, H:i') }}</span>
                            </div>
                            <span class="inc-badge" style="background:{{ $confrontation->manager_verdict ? 'var(--mist)' : 'var(--gold-light)' }};color:var(--navy);border:1px solid var(--navy);">
                                {{ $confrontation->manager_verdict ? \App\Helpers\Label::confrontationVerdict($confrontation->manager_verdict) : ($confrontation->staff_response ? 'AWAITING YOUR VERDICT' : 'ON MANAGER DESK') }}
                            </span>
                        </div>

                        <div style="display:flex;gap:14px;margin-top:10px;">
                            <div style="flex:1;background:var(--pin-white);border-radius:8px;padding:8px 10px;border:1px solid var(--fog);">
                                <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">REPORTER</div>
                                <div style="font-family:var(--font-sub);font-size:0.72rem;">{{ $confrontation->reporter->user->name ?? '—' }}</div>
                            </div>
                            <div style="align-self:center;font-size:1rem;color:var(--coral);">&#8594;</div>
                            <div style="flex:1;background:var(--pin-white);border-radius:8px;padding:8px 10px;border:1px solid var(--fog);">
                                <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">ACCUSED CARETAKER</div>
                                <div style="font-family:var(--font-sub);font-size:0.72rem;">{{ $confrontation->accused->user->name ?? '—' }}</div>
                            </div>
                        </div>

                        @if ($confrontation->incident_description)
                            <div style="font-family:var(--font-body);font-size:0.7rem;margin-top:8px;background:var(--mist);border-radius:8px;padding:8px 10px;">{{ $confrontation->incident_description }}</div>
                        @endif
                    </div>
                @empty
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;text-align:center;">
                        <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">No incident reports on file.</span>
                    </div>
                @endforelse
            </div>

        </div>
    </div>

    <x-toast />

    <script>
    (function () {
        var form = document.getElementById('incForm');
        if (!form) return;
        var csrf = form.querySelector('input[name="_token"]').value;
        var list = document.getElementById('incList');

        function esc(s) {
            var d = document.createElement('div');
            d.textContent = s == null ? '' : String(s);
            return d.innerHTML;
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var reporter = document.getElementById('incReporter').value;
            var accused = document.getElementById('incAccused').value;
            if (!reporter || !accused) return;

            var payload = {
                reporter_staff_id: reporter,
                accused_staff_id: accused,
                incident_type: document.getElementById('incType').value,
                incident_description: document.getElementById('incDesc').value.trim(),
                db_verified: document.getElementById('incVerified').checked
            };

            var btn = document.getElementById('incSubmit');
            btn.disabled = true;

            fetch(@json(route('steward.incidents.store')), {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify(payload)
            })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (res) {
                btn.disabled = false;
                if (!res.ok) {
                    alert(res.data.message || res.data.error || 'Could not log the report.');
                    return;
                }
                var d = res.data;
                var empty = list.querySelector('span');
                var emptyCard = empty && empty.textContent.trim() === 'No incident reports on file.' ? empty.closest('.inc-card') : null;
                if (emptyCard) emptyCard.remove();

                var row = document.createElement('div');
                row.className = 'inc-card inc-row';
                row.innerHTML =
                    '<div style="display:flex;justify-content:space-between;align-items:center;">' +
                        '<div style="display:flex;gap:8px;align-items:center;">' +
                            '<span class="inc-badge" style="background:var(--navy);color:var(--pin-white);">' + esc(d.incident_label) + '</span>' +
                            (d.db_verified ? '<span class="inc-badge" style="background:var(--sky);color:var(--sky-dark);border:1px solid var(--sky-dark);">VERIFIED</span>' : '') +
                            '<span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">' + esc(d.date) + '</span>' +
                        '</div>' +
                        '<span class="inc-badge" style="background:var(--gold-light);color:var(--navy);border:1px solid var(--navy);">ON MANAGER DESK</span>' +
                    '</div>' +
                    '<div style="display:flex;gap:14px;margin-top:10px;">' +
                        '<div style="flex:1;background:var(--pin-white);border-radius:8px;padding:8px 10px;border:1px solid var(--fog);"><div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">REPORTER</div><div style="font-family:var(--font-sub);font-size:0.72rem;">' + esc(d.reporter) + '</div></div>' +
                        '<div style="align-self:center;font-size:1rem;color:var(--coral);">&#8594;</div>' +
                        '<div style="flex:1;background:var(--pin-white);border-radius:8px;padding:8px 10px;border:1px solid var(--fog);"><div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">ACCUSED CARETAKER</div><div style="font-family:var(--font-sub);font-size:0.72rem;">' + esc(d.accused) + '</div></div>' +
                    '</div>' +
                    (d.description ? '<div style="font-family:var(--font-body);font-size:0.7rem;margin-top:8px;background:var(--mist);border-radius:8px;padding:8px 10px;">' + esc(d.description) + '</div>' : '');

                list.insertBefore(row, list.firstChild);

                document.getElementById('incDesc').value = '';
                document.getElementById('incVerified').checked = false;
            })
            .catch(function () { btn.disabled = false; });
        });
    })();
    </script>

    @include('sim.partials.responsive')
</x-app-layout>
