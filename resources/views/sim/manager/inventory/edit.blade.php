<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Edit {{ $inventory->name }}</h2>
            <div style="display:flex;align-items:center;gap:1rem;">
                <span class="badge-role manager">Manager</span>
            </div>
        </div>
    </x-slot>

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">
        @include('sim.partials.module-dock')
        <div style="padding:1.25rem;overflow:hidden;">
            <form method="POST" action="{{ route('manager.inventory.update', $inventory) }}" style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1.25rem;display:grid;gap:14px;max-width:560px;">
                @csrf
                @method('PUT')

                <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;">
                    <div>
                        <label class="input-label" for="name" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Name</label>
                        <input id="name" name="name" type="text" required value="{{ old('name', $inventory->name) }}" style="width:100%;font-family:var(--font-body);font-size:0.85rem;padding:8px 12px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                    </div>
                    <div>
                        <label class="input-label" for="category" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Category</label>
                        <input id="category" name="category" type="text" required value="{{ old('category', $inventory->category) }}" style="width:100%;font-family:var(--font-body);font-size:0.85rem;padding:8px 12px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
                    <div class="range-wrap">
                        <label class="input-label" for="quantity" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Quantity</label>
                        <input id="quantity" name="quantity" type="range" class="lane-range" min="0" max="500" step="1" value="{{ old('quantity', $inventory->quantity) }}" data-label="Qty" data-unit="units">
                        <div class="range-read"></div>
                    </div>
                    <div>
                        <label class="input-label" for="max_quantity" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Max Quantity</label>
                        <input id="max_quantity" name="max_quantity" type="number" min="1" required data-stepper="edit" value="{{ old('max_quantity', $inventory->max_quantity) }}" style="width:100%;font-family:var(--font-body);font-size:0.85rem;padding:8px 12px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                    </div>
                    <div>
                        <label class="input-label" for="reorder_threshold" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Reorder Threshold</label>
                        <input id="reorder_threshold" name="reorder_threshold" type="number" min="0" required data-stepper="edit" value="{{ old('reorder_threshold', $inventory->reorder_threshold) }}" style="width:100%;font-family:var(--font-body);font-size:0.85rem;padding:8px 12px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                    </div>
                    <div>
                        <label class="input-label" for="cost_per_unit" style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);text-transform:uppercase;">Cost / Unit ($)</label>
                        <input id="cost_per_unit" name="cost_per_unit" type="number" step="0.01" min="0" required data-stepper="edit" value="{{ old('cost_per_unit', $inventory->cost_per_unit) }}" style="width:100%;font-family:var(--font-body);font-size:0.85rem;padding:8px 12px;border:2px solid var(--navy);border-radius:8px;background:var(--pin-white);">
                    </div>
                </div>

                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn-lane primary" style="font-size:0.65rem;padding:7px 18px;">Save</button>
                    <a href="{{ route('manager.inventory.index') }}" class="btn-lane secondary" style="font-size:0.65rem;padding:7px 18px;">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <x-toast />

    @include('sim.partials.fold-controls')
    @include('sim.partials.responsive')
</x-app-layout>
