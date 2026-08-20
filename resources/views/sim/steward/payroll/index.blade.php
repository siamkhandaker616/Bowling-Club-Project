<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Crew Payroll</h2>
        </div>
    </x-slot>

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div style="display:flex;flex-direction:column;gap:1.2rem;">
            <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;display:flex;gap:1.5rem;flex-wrap:wrap;">
                <div>
                    <div style="font-family:var(--font-mono);font-size:1rem;font-weight:700;color:var(--navy);">${{ number_format($dailyCost, 2) }}</div>
                    <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);letter-spacing:1px;">DAILY PAYROLL BURN</div>
                </div>
                <div>
                    <div style="font-family:var(--font-mono);font-size:1rem;font-weight:700;color:{{ $cuts > 0 ? 'var(--coral)' : 'var(--sky-dark)' }};">{{ $cuts }}</div>
                    <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);letter-spacing:1px;">CREW ON DOCKED PAY</div>
                </div>
                <div>
                    <div style="font-family:var(--font-mono);font-size:1rem;font-weight:700;color:var(--slate);">1.5&times;</div>
                    <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);letter-spacing:1px;">CEILING OVER BASE</div>
                </div>
            </div>

            <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
                    <span class="dash-section-label" style="margin:0;">Salary Desk</span>
                    <span class="badge sky">{{ $crew->count() }} CREW</span>
                </div>

                <div id="payrollList" style="display:flex;flex-direction:column;gap:0.6rem;">
                    @forelse ($crew as $member)
                        @php
                            $docked = (float) $member->current_salary < (float) $member->base_salary;
                            $ceiling = round((float) $member->base_salary * 1.5, 2);
                        @endphp
                        <div class="pay-row" data-id="{{ $member->id }}" data-base="{{ $member->base_salary }}" data-ceiling="{{ $ceiling }}" style="border:2px solid {{ $docked ? 'var(--coral)' : 'var(--navy)' }};border-radius:12px;padding:0.8rem 1rem;background:var(--pin-white);">
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:0.75rem;flex-wrap:wrap;">
                                <div style="min-width:180px;">
                                    <span style="font-family:var(--font-sub);font-size:0.8rem;color:var(--navy);font-weight:700;">{{ $member->user->name ?? 'Staff' }}</span>
                                    <span class="badge {{ match($member->role) { 'steward' => 'sky', 'caretaker' => 'gold', default => 'coral' } }}" style="font-size:0.5rem;margin-left:6px;">{{ strtoupper($member->role) }}</span>
                                    @if ($docked)
                                        <span class="badge coral" style="font-size:0.5rem;margin-left:4px;">DOCKED</span>
                                    @endif
                                </div>
                                <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">
                                    BASE ${{ number_format($member->base_salary, 0) }} · NOW <span class="pay-now" style="color:{{ $docked ? 'var(--coral)' : 'var(--navy)' }};font-weight:700;">${{ number_format($member->current_salary, 0) }}</span> /mo
                                </div>
                                <div style="display:flex;gap:0.4rem;align-items:center;" class="pay-actions">
                                    <input type="number" min="0" step="10" value="{{ $member->current_salary }}" class="input pay-input" data-id="{{ $member->id }}" style="width:110px;margin:0;">
                                    <button type="button" class="btn btn-xs pay-apply" data-id="{{ $member->id }}">Apply</button>
                                    <button type="button" class="btn btn-ghost btn-xs pay-restore" data-id="{{ $member->id }}" data-base="{{ $member->base_salary }}">Restore base</button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center;padding:1.5rem;">
                            <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">No active crew on the books.</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <x-toast />

    <script>
        const csrf = '{{ csrf_token() }}';
        const payrollRows = document.querySelectorAll('.pay-row');

        function post(url, payload) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify(payload),
            }).then(async (res) => ({ ok: res.ok, data: await res.json().catch(() => ({})) }));
        }

        function applyRow(row, salary) {
            return post(`/steward/payroll/${row.dataset.id}`, { salary: Number(salary) });
        }

        document.querySelectorAll('.pay-apply').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const row = btn.closest('.pay-row');
                const input = row.querySelector('.pay-input');
                btn.disabled = true;
                const { ok, data } = await applyRow(row, input.value);
                btn.disabled = false;
                if (!ok || !data.ok) {
                    alert(data.message || 'Could not update salary.');
                    return;
                }
                input.value = data.salary;
                row.querySelector('.pay-now').textContent = '$' + Number(data.salary).toLocaleString();
                if (Number(data.salary) < Number(row.dataset.base)) {
                    row.style.borderColor = 'var(--coral)';
                } else {
                    row.style.borderColor = 'var(--navy)';
                }
            });
        });

        document.querySelectorAll('.pay-restore').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const row = btn.closest('.pay-row');
                const input = row.querySelector('.pay-input');
                input.value = row.dataset.base;
                btn.disabled = true;
                const { ok, data } = await applyRow(row, row.dataset.base);
                btn.disabled = false;
                if (!ok || !data.ok) {
                    alert(data.message || 'Could not restore salary.');
                    return;
                }
                row.querySelector('.pay-now').textContent = '$' + Number(data.salary).toLocaleString();
                row.style.borderColor = 'var(--navy)';
            });
        });
    </script>

    @include('sim.partials.responsive')
</x-app-layout>
