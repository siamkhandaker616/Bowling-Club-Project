<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Reapply</h2>
            <span class="badge-role caretaker">Fired Staff</span>
        </div>
    </x-slot>

    <div style="display:grid;grid-template-columns:200px 1fr;gap:0;min-height:calc(100vh - 200px);">
        <div class="dash-sidebar">
            <div class="dash-section-label" style="margin-bottom:4px;">You were let go</div>
            <div style="font-family:var(--font-body);font-size:0.7rem;line-height:1.5;color:var(--slate);padding:0 0.25rem;">
                Your employment was terminated. You can reapply with a new identity and a new role — no hard feelings.
            </div>
            <div class="dash-section-label" style="margin-top:16px;">Last Known As</div>
            <div class="dash-stat" style="margin-top:8px;">
                <span class="dash-stat-num">{{ $staff->user->name }}</span>
                <span class="dash-stat-label">{{ $staff->role }}</span>
            </div>
            <div class="dash-stat" style="margin-top:6px;">
                <span class="dash-stat-num" style="color:var(--coral-dark);">{{ $staff->happiness }}</span>
                <span class="dash-stat-label">Morale when you left</span>
            </div>
        </div>

        <div style="padding:1.25rem;overflow:hidden;">
            <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1.25rem;max-width:520px;">
                <div class="dash-section-label">New Identity Application</div>
                <form method="POST" action="{{ route('reapply.store') }}" style="display:flex;flex-direction:column;gap:10px;margin-top:10px;">
                    @csrf
                    <div>
                        <label style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">New Name</label>
                        <input name="name" type="text" value="{{ old('name') }}" required maxlength="255"
                               style="width:100%;font-family:var(--font-body);font-size:0.75rem;padding:8px 10px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                    </div>
                    <div>
                        <label style="font-family:var(--font-mono);font-size:0.6rem;color:var(--slate);">New Role</label>
                        <select name="role" required class="con-input fold-select" style="width:100%;font-family:var(--font-body);font-size:0.75rem;padding:8px 10px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                            <option value="caretaker" @selected(old('role') === 'caretaker')>Caretaker</option>
                            <option value="steward" @selected(old('role') === 'steward')>Steward</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-lane primary" style="font-size:0.65rem;padding:8px 12px;">Reapply</button>
                    <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">
                        Reapplying resets your morale to 70 and reactivates your account. Your old record stays in the staff history.
                    </span>
                </form>
            </div>
        </div>
    </div>

    <x-toast />

    @include('sim.partials.fold-controls')
    @include('sim.partials.responsive')
</x-app-layout>
