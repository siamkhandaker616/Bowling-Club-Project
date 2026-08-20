<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Complaints & Compensation</h2>

        </div>
    </x-slot>

    <style>
        .comp-card{background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;display:flex;gap:14px;align-items:flex-start;}
        .comp-badge{font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;}
    </style>

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div style="padding:1.25rem;overflow:hidden;">

            <div style="display:flex;flex-direction:column;gap:10px;">
                @forelse ($complaints as $complaint)
                    <div class="comp-card">
                        <div style="flex:1;">
                            <div style="display:flex;gap:8px;align-items:center;">
                                <span class="comp-badge" style="background:{{ match($complaint->status) { 'open' => 'var(--coral-light)', 'investigating' => 'var(--gold-light)', 'resolved' => 'var(--sky)', default => 'var(--mist)' } }};color:var(--navy);border:1px solid var(--navy);">{{ \App\Helpers\Label::complaintStatus($complaint->status) }}</span>
                                <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">{{ \App\Helpers\Label::complaintType($complaint->type) }} · {{ $complaint->created_at->format('M j, H:i') }}</span>
                            </div>
                            <div style="font-family:var(--font-body);font-size:0.72rem;color:var(--navy);margin-top:6px;background:var(--pin-white);border-radius:8px;padding:8px 10px;border:1px solid var(--fog);">{{ $complaint->description }}</div>
                            <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);margin-top:6px;">
                                Visitor: {{ $complaint->visitor?->name ?? '—' }}
                                @if ($complaint->staff)
                                    · Staff: {{ $complaint->staff->user->name }}
                                @endif
                                @if ($complaint->raisedBy)
                                    · Raised by: {{ $complaint->raisedBy->user->name }}
                                @endif
                            </div>
                            @if ($complaint->resolution)
                                <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--sky-dark);margin-top:4px;">Resolution: {{ $complaint->resolution }}@if ($complaint->compensation_type) ({{ \App\Helpers\Label::compensationType($complaint->compensation_type) }})@endif</div>
                            @endif
                        </div>

                        @if ($complaint->status === 'investigating')
                            <div class="comp-actions" data-id="{{ $complaint->id }}" style="min-width:220px;display:flex;flex-direction:column;gap:6px;">
                                <textarea name="resolution" id="res-{{ $complaint->id }}" placeholder="Resolution..." class="input textarea"></textarea>
                                <div class="select-wrap">
                                    <select name="compensation_type" id="comp-{{ $complaint->id }}" class="input select">
                                        <option value="">No compensation</option>
                                        <option value="free_game">Free Game</option>
                                        <option value="refund">Refund</option>
                                        <option value="discount">Discount</option>
                                        <option value="apology">Apology</option>
                                        <option value="priority_queue">Priority Queue</option>
                                    </select>
                                    <span class="select-arrow">&#9662;</span>
                                </div>
                                <div style="display:flex;gap:6px;">
                                    <button type="button" class="btn-lane primary comp-resolve" data-id="{{ $complaint->id }}" data-url="{{ route('manager.complaints.resolve', $complaint) }}" style="flex:1;font-size:0.55rem;padding:5px 10px;">Resolve</button>
                                    <button type="button" class="btn-lane secondary comp-dismiss" data-id="{{ $complaint->id }}" data-url="{{ route('manager.complaints.dismiss', $complaint) }}" style="flex:1;font-size:0.55rem;padding:5px 10px;">Dismiss</button>
                                </div>
                            </div>
                        @elseif ($complaint->status === 'open')
                            <span style="min-width:170px;max-width:190px;text-align:right;font-family:var(--font-mono);font-size:0.55rem;color:var(--coral-dark);">&#128274; AWAITING STEWARD<br>ESCALATION</span>
                        @endif
                    </div>
                @empty
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;text-align:center;">
                        <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">No complaints logged.</span>
                    </div>
                @endforelse
            </div>

        </div>
    </div>

    <x-toast />

    <script>
    (function () {
        var csrf = @json(csrf_token());

        function closeActions(id, okHtml) {
            var actions = document.querySelector('.comp-actions[data-id="' + id + '"]');
            if (!actions) return;
            var wrap = document.createElement('div');
            wrap.style.minWidth = '220px';
            wrap.style.display = 'flex';
            wrap.style.flexDirection = 'column';
            wrap.style.gap = '4px';
            wrap.innerHTML = okHtml;
            actions.replaceWith(wrap);
        }

        function post(url, body, btn, onOk) {
            btn.disabled = true;
            fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify(body)
            })
            .then(function (r) {
                if (!r.ok) { throw new Error('blocked'); }
                return r.json();
            })
            .then(function (d) { onOk(d); })
            .catch(function () { btn.disabled = false; });
        }

        document.querySelectorAll('.comp-resolve').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = btn.getAttribute('data-id');
                var res = document.getElementById('res-' + id);
                var body = { resolution: (res ? res.value : '').trim() };
                if (!body.resolution) { if (res) res.style.borderColor = 'var(--coral)'; return; }
                var comp = document.getElementById('comp-' + id);
                if (comp && comp.value) body.compensation_type = comp.value;
                post(btn.getAttribute('data-url'), body, btn, function (d) {
                    closeActions(id,
                        '<div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--ok);">RESOLVED · ' + (d.compensation_label || 'NO COMPENSATION') + '</div>' +
                        '<div style="font-family:var(--font-body);font-size:0.62rem;color:var(--slate);">' + d.resolution + '</div>'
                    );
                });
            });
        });

        document.querySelectorAll('.comp-dismiss').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = btn.getAttribute('data-id');
                post(btn.getAttribute('data-url'), {}, btn, function (d) {
                    closeActions(id,
                        '<div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">DISMISSED</div>' +
                        '<div style="font-family:var(--font-body);font-size:0.62rem;color:var(--slate);">' + d.resolution + '</div>'
                    );
                });
            });
        });
    })();
    </script>

    @include('sim.partials.fold-controls')
    @include('sim.partials.responsive')
</x-app-layout>
