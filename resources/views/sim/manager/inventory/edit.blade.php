<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Edit {{ $inventory->name }}</h2>

        </div>
    </x-slot>

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">
        @include('sim.partials.module-dock')
        <div style="padding:1.25rem;overflow:hidden;">
            <form method="POST" action="{{ route('manager.inventory.update', $inventory) }}" class="gutter-form" style="max-width:560px;">
                @csrf
                @method('PUT')

                <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;">
                    <div class="gutter-field field">
                        <label class="label" for="name">Name <span class="req">*</span></label>
                        <input id="name" name="name" class="input" type="text" value="{{ old('name', $inventory->name) }}">
                        <div class="gutter-err">Name is required</div>
                        <div class="gutter-flag">&#10003;</div>
                    </div>
                    <div class="gutter-field field">
                        <label class="label" for="category">Category <span class="req">*</span></label>
                        <input id="category" name="category" class="input" type="text" value="{{ old('category', $inventory->category) }}">
                        <div class="gutter-err">Category is required</div>
                        <div class="gutter-flag">&#10003;</div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
                    <div class="range-wrap field">
                        <label class="label" for="quantity">Quantity</label>
                        <input id="quantity" name="quantity" type="range" class="lane-range" min="0" max="500" step="1" value="{{ old('quantity', $inventory->quantity) }}" data-label="Qty" data-unit="units">
                        <div class="range-read"></div>
                    </div>
                    <div class="gutter-field field">
                        <label class="label" for="max_quantity">Max Quantity <span class="req">*</span></label>
                        <input id="max_quantity" name="max_quantity" class="input" type="number" min="1" data-stepper="edit" value="{{ old('max_quantity', $inventory->max_quantity) }}">
                        <div class="gutter-err">Min 1 required</div>
                        <div class="gutter-flag">&#10003;</div>
                    </div>
                    <div class="gutter-field field">
                        <label class="label" for="reorder_threshold">Restock Level <span class="req">*</span></label>
                        <input id="reorder_threshold" name="reorder_threshold" class="input" type="number" min="0" data-stepper="edit" value="{{ old('reorder_threshold', $inventory->reorder_threshold) }}">
                        <div class="gutter-err">Min 0 required</div>
                        <div class="gutter-flag">&#10003;</div>
                    </div>
                    <div class="gutter-field field">
                        <label class="label" for="cost_per_unit">Cost / Unit (৳) <span class="req">*</span></label>
                        <input id="cost_per_unit" name="cost_per_unit" class="input" type="number" step="0.01" min="0" data-stepper="edit" value="{{ old('cost_per_unit', $inventory->cost_per_unit) }}">
                        <div class="gutter-err">Min 0 required</div>
                        <div class="gutter-flag">&#10003;</div>
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
