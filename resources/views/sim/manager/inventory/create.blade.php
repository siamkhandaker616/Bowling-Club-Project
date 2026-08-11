<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Add Inventory Item</h2>
            <div style="display:flex;align-items:center;gap:1rem;">
                <span class="badge-role manager">Manager</span>
            </div>
        </div>
    </x-slot>

    <div style="zoom:1.25;display:grid;grid-template-columns:200px 1fr;gap:0;min-height:calc(100vh - 200px);">
        <div class="dash-sidebar">
            <div class="dash-section-label" style="margin-bottom:4px;">Modules</div>
            <a href="{{ route('manager.dashboard') }}" class="dash-sidebar-link">Overview</a>
            <a href="{{ route('manager.staff.index') }}" class="dash-sidebar-link">Staff</a>
            <a href="{{ route('manager.inventory.index') }}" class="dash-sidebar-link active">Inventory</a>
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
            <form method="POST" action="{{ route('manager.inventory.store') }}" style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1.25rem;display:grid;gap:14px;max-width:560px;">
                @csrf

                <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;">
                    <div>
                        <label class="input-label" for="name" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Name</label>
                        <input id="name" name="name" type="text" required value="{{ old('name') }}" style="width:100%;font-family:var(--font-body);font-size:0.85rem;padding:8px 12px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                    </div>
                    <div>
                        <label class="input-label" for="category" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Category</label>
                        <input id="category" name="category" type="text" required value="{{ old('category') }}" style="width:100%;font-family:var(--font-body);font-size:0.85rem;padding:8px 12px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
                    <div>
                        <label class="input-label" for="quantity" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Quantity</label>
                        <input id="quantity" name="quantity" type="number" min="0" required value="{{ old('quantity', 0) }}" style="width:100%;font-family:var(--font-body);font-size:0.85rem;padding:8px 12px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                    </div>
                    <div>
                        <label class="input-label" for="max_quantity" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Max Quantity</label>
                        <input id="max_quantity" name="max_quantity" type="number" min="1" required value="{{ old('max_quantity', 50) }}" style="width:100%;font-family:var(--font-body);font-size:0.85rem;padding:8px 12px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                    </div>
                    <div>
                        <label class="input-label" for="reorder_threshold" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Reorder Threshold</label>
                        <input id="reorder_threshold" name="reorder_threshold" type="number" min="0" required value="{{ old('reorder_threshold', 10) }}" style="width:100%;font-family:var(--font-body);font-size:0.85rem;padding:8px 12px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                    </div>
                    <div>
                        <label class="input-label" for="cost_per_unit" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Cost / Unit ($)</label>
                        <input id="cost_per_unit" name="cost_per_unit" type="number" step="0.01" min="0" required value="{{ old('cost_per_unit', 10) }}" style="width:100%;font-family:var(--font-body);font-size:0.85rem;padding:8px 12px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                    </div>
                </div>

                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn-lane primary" style="font-size:0.65rem;padding:7px 18px;">Add Item</button>
                    <a href="{{ route('manager.inventory.index') }}" class="btn-lane secondary" style="font-size:0.65rem;padding:7px 18px;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
