<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Edit {{ $staff->user->name }}</h2>
            <div style="display:flex;align-items:center;gap:1rem;">
                <span class="badge-role manager">Manager</span>
            </div>
        </div>
    </x-slot>

    <div style="zoom:1.25;display:grid;grid-template-columns:200px 1fr;gap:0;min-height:calc(100vh - 200px);">
        <div class="dash-sidebar">
            <div class="dash-section-label" style="margin-bottom:4px;">Modules</div>
            <a href="{{ route('manager.dashboard') }}" class="dash-sidebar-link">Overview</a>
            <a href="{{ route('manager.staff.index') }}" class="dash-sidebar-link active">Staff</a>
            <a href="{{ route('manager.inventory.index') }}" class="dash-sidebar-link">Inventory</a>
            <a href="{{ route('manager.bookings.index') }}" class="dash-sidebar-link">Bookings</a>
            <a href="{{ route('manager.bans.index') }}" class="dash-sidebar-link">Bans</a>
            <a href="{{ route('manager.complaints.index') }}" class="dash-sidebar-link">Complaints</a>
            <a href="{{ route('manager.confrontations.index') }}" class="dash-sidebar-link">Confrontations</a>
            <a href="{{ route('manager.reviews.index') }}" class="dash-sidebar-link">Reviews</a>
            <a href="{{ route('manager.touring.index') }}" class="dash-sidebar-link">Touring</a>
            <div style="margin-top:auto;padding-top:0.75rem;border-top:2px solid var(--fog);text-align:center;">
                <div class="ball-avatar ball-sm ball-navy" style="margin:0 auto;"><div class="ball-holes"><span></span><span></span><span></span></div><span class="ball-initials">SK</span></div>
                <div style="font-family:var(--font-sub);font-size:0.65rem;margin-top:4px;">{{ ucfirst(Auth::user()->name) }}</div>
                <span class="badge-role manager" style="font-size:0.5rem;padding:2px 8px;">Manager</span>
            </div>
        </div>
        <div style="padding:1.25rem;overflow:hidden;">
            <form method="POST" action="{{ route('manager.staff.update', $staff) }}" style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1.25rem;display:grid;gap:14px;max-width:640px;">
                @csrf
                @method('PUT')

                <div>
                    <label class="input-label" for="name" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Full Name</label>
                    <input id="name" name="name" type="text" required value="{{ old('name', $staff->user->name) }}" style="width:100%;font-family:var(--font-body);font-size:0.85rem;padding:8px 12px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label class="input-label" for="role" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Role</label>
                        <select id="role" name="role" style="width:100%;font-family:var(--font-body);font-size:0.85rem;padding:8px 12px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                            <option value="caretaker" @selected($staff->role === 'caretaker')>Caretaker</option>
                            <option value="steward" @selected($staff->role === 'steward')>Steward</option>
                        </select>
                    </div>
                    <div>
                        <label class="input-label" for="is_active" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Status</label>
                        <select id="is_active" name="is_active" style="width:100%;font-family:var(--font-body);font-size:0.85rem;padding:8px 12px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                            <option value="1" @selected($staff->is_active)>Active</option>
                            <option value="0" @selected(! $staff->is_active)>Inactive</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label class="input-label" for="base_salary" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Base Salary ($)</label>
                        <input id="base_salary" name="base_salary" type="number" step="0.01" min="0" required value="{{ old('base_salary', $staff->base_salary) }}" style="width:100%;font-family:var(--font-body);font-size:0.85rem;padding:8px 12px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                    </div>
                    <div>
                        <label class="input-label" for="current_salary" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Current Salary ($)</label>
                        <input id="current_salary" name="current_salary" type="number" step="0.01" min="0" required value="{{ old('current_salary', $staff->current_salary) }}" style="width:100%;font-family:var(--font-body);font-size:0.85rem;padding:8px 12px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                    <div>
                        <label class="input-label" for="happiness" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Happiness</label>
                        <input id="happiness" name="happiness" type="number" min="0" max="100" required value="{{ old('happiness', $staff->happiness) }}" style="width:100%;font-family:var(--font-body);font-size:0.85rem;padding:8px 12px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                    </div>
                    <div>
                        <label class="input-label" for="performance_score" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Performance</label>
                        <input id="performance_score" name="performance_score" type="number" min="0" max="100" required value="{{ old('performance_score', $staff->performance_score) }}" style="width:100%;font-family:var(--font-body);font-size:0.85rem;padding:8px 12px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                    </div>
                    <div>
                        <label class="input-label" for="honesty_score" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Honesty</label>
                        <input id="honesty_score" name="honesty_score" type="number" min="0" max="100" required value="{{ old('honesty_score', $staff->honesty_score) }}" style="width:100%;font-family:var(--font-body);font-size:0.85rem;padding:8px 12px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                    </div>
                </div>

                <div>
                    <label class="input-label" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Personalities</label>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:6px;margin-top:6px;">
                        @foreach ($personalities as $p)
                            <label style="display:flex;align-items:center;gap:8px;padding:6px 10px;background:var(--pin-white);border:2px solid var(--fog);border-radius:8px;font-family:var(--font-body);font-size:0.72rem;cursor:pointer;">
                                <input type="checkbox" name="personalities[]" value="{{ $p->id }}" @checked($staff->personalities->contains('id', $p->id))>
                                <span style="font-family:var(--font-mono);font-size:0.6rem;">{{ $p->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div style="display:flex;gap:10px;align-items:center;">
                    <button type="submit" class="btn-lane primary" style="font-size:0.65rem;padding:7px 18px;">Save</button>
                    <a href="{{ route('manager.staff.show', $staff) }}" class="btn-lane secondary" style="font-size:0.65rem;padding:7px 18px;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
