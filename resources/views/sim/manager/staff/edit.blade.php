<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Edit {{ $staff->user->name }}</h2>

        </div>
    </x-slot>

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">
        @include('sim.partials.module-dock')
        <div style="padding:1.25rem;overflow:hidden;">
            <form method="POST" action="{{ route('manager.staff.update', $staff) }}" novalidate class="gutter-form" style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1.25rem;display:grid;gap:14px;max-width:640px;">
                @csrf
                @method('PUT')

                <div class="gutter-field">
                    <label class="input-label" for="name" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Full Name</label>
                    <div class="inp-wrap">
                        <input id="name" name="name" type="text" required value="{{ old('name', $staff->user->name) }}" class="input{{ $errors->has('name') ? ' bad' : '' }}">
                        <span class="gutter-flag">&#10003;</span>
                    </div>
                    <div class="gutter-err">@error('name'){{ $message }}@else Name is required @enderror</div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label class="input-label" for="role" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Role</label>
                        <select id="role" name="role" class="fold-select" style="width:100%;font-family:var(--font-body);font-size:0.85rem;padding:8px 12px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                            <option value="caretaker" @selected($staff->role === 'caretaker')>Caretaker</option>
                            <option value="steward" @selected($staff->role === 'steward')>Steward</option>
                        </select>
                    </div>
                    <div>
                        <label class="input-label" for="is_active" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Status</label>
                        <select id="is_active" name="is_active" class="fold-select" style="width:100%;font-family:var(--font-body);font-size:0.85rem;padding:8px 12px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                            <option value="1" @selected($staff->is_active)>Active</option>
                            <option value="0" @selected(! $staff->is_active)>Inactive</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="gutter-field">
                        <label class="input-label" for="base_salary" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Base Salary ($)</label>
                        <div class="inp-wrap">
                            <input id="base_salary" name="base_salary" type="number" step="0.01" min="0" required data-stepper="edit" value="{{ old('base_salary', $staff->base_salary) }}" class="input{{ $errors->has('base_salary') ? ' bad' : '' }}">
                            <span class="gutter-flag">&#10003;</span>
                        </div>
                        <div class="gutter-err">@error('base_salary'){{ $message }}@else Salary is required @enderror</div>
                    </div>
                    <div class="gutter-field">
                        <label class="input-label" for="current_salary" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Current Salary ($)</label>
                        <div class="inp-wrap">
                            <input id="current_salary" name="current_salary" type="number" step="0.01" min="0" required data-stepper="edit" value="{{ old('current_salary', $staff->current_salary) }}" class="input{{ $errors->has('current_salary') ? ' bad' : '' }}">
                            <span class="gutter-flag">&#10003;</span>
                        </div>
                        <div class="gutter-err">@error('current_salary'){{ $message }}@else Current salary is required @enderror</div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                    <div class="gutter-field">
                        <label class="input-label" for="happiness" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Happiness</label>
                        <div class="inp-wrap">
                            <input id="happiness" name="happiness" type="number" min="0" max="100" required data-stepper="edit" value="{{ old('happiness', $staff->happiness) }}" class="input{{ $errors->has('happiness') ? ' bad' : '' }}">
                            <span class="gutter-flag">&#10003;</span>
                        </div>
                        <div class="gutter-err">@error('happiness'){{ $message }}@else Happiness is required @enderror</div>
                    </div>
                    <div class="gutter-field">
                        <label class="input-label" for="performance_score" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Performance</label>
                        <div class="inp-wrap">
                            <input id="performance_score" name="performance_score" type="number" min="0" max="100" required data-stepper="edit" value="{{ old('performance_score', $staff->performance_score) }}" class="input{{ $errors->has('performance_score') ? ' bad' : '' }}">
                            <span class="gutter-flag">&#10003;</span>
                        </div>
                        <div class="gutter-err">@error('performance_score'){{ $message }}@else Performance score is required @enderror</div>
                    </div>
                    <div class="gutter-field">
                        <label class="input-label" for="honesty_score" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Honesty</label>
                        <div class="inp-wrap">
                            <input id="honesty_score" name="honesty_score" type="number" min="0" max="100" required data-stepper="edit" value="{{ old('honesty_score', $staff->honesty_score) }}" class="input{{ $errors->has('honesty_score') ? ' bad' : '' }}">
                            <span class="gutter-flag">&#10003;</span>
                        </div>
                        <div class="gutter-err">@error('honesty_score'){{ $message }}@else Honesty score is required @enderror</div>
                    </div>
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

                <div style="display:flex;gap:10px;align-items:center;">
                    <button type="submit" class="btn-lane primary" style="font-size:0.65rem;padding:7px 18px;">Save</button>
                    <a href="{{ route('manager.staff.show', $staff) }}" class="btn-lane secondary" style="font-size:0.65rem;padding:7px 18px;">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <x-toast />

    @include('sim.partials.fold-controls')
    @include('sim.partials.responsive')
</x-app-layout>
