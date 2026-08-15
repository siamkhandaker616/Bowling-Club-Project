<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Inventory Management</h2>
            <div style="display:flex;align-items:center;gap:1rem;">
                <span class="badge-role manager">Manager</span>
            </div>
        </div>
    </x-slot>

    <style>
        .sim-stock{height:10px;background:var(--fog);border-radius:5px;overflow:hidden;min-width:120px;}
        .sim-stock > div{height:100%;border-radius:5px;}
    </style>

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div style="padding:1.25rem;overflow:hidden;">

            @if ($lowStock->count())
                <div style="margin-bottom:1rem;">
                    @foreach ($lowStock as $item)
                        @include('sim.partials.bubble', ['type' => 'warn', 'title' => 'Low stock: ' . $item->name, 'message' => $item->quantity . ' left (threshold ' . $item->reorder_threshold . '). Restock soon — low supplies hurt the club.'])
                    @endforeach
                </div>
            @endif

            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <div class="dash-section-label" style="margin:0;">Stock Levels</div>
                <a href="{{ route('manager.inventory.create') }}" class="btn-lane primary" style="font-size:0.6rem;padding:5px 14px;">+ Add Item</a>
            </div>

            <div style="display:flex;flex-direction:column;gap:10px;">
                @forelse ($items as $item)
                    @php
                        $pct = round($item->quantity / max(1, $item->max_quantity) * 100);
                        $barColor = $item->isLowStock() ? 'var(--coral)' : ($pct < 40 ? 'var(--gold)' : 'var(--sky-dark)');
                    @endphp
                    <div style="display:flex;align-items:center;gap:14px;padding:12px 14px;background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;">
                        <div style="flex:1;min-width:180px;">
                            <div style="font-family:var(--font-sub);font-size:0.75rem;">{{ $item->name }}</div>
                            <div style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">{{ $item->category }} · ${{ $item->cost_per_unit }}/unit · {{ $item->condition }}</div>
                        </div>
                        <div style="flex:1;">
                            <div style="display:flex;justify-content:space-between;font-family:var(--font-mono);font-size:0.6rem;margin-bottom:4px;">
                                <span>{{ $item->quantity }} / {{ $item->max_quantity }}</span>
                                <span style="color:{{ $barColor }};">{{ $pct }}%</span>
                            </div>
                            <div class="sim-stock"><div style="width:{{ $pct }}%;background:{{ $barColor }};"></div></div>
                        </div>
                        <div style="display:flex;gap:6px;align-items:center;">
                            <form method="POST" action="{{ route('manager.inventory.adjust', $item) }}">
                                @csrf
                                <input name="change" type="number" placeholder="±qty" data-stepper="edit" style="width:70px;font-family:var(--font-mono);font-size:0.65rem;padding:4px 8px;border:2px solid var(--navy);border-radius:6px;">
                                <button type="submit" style="font-family:var(--font-mono);font-size:0.55rem;padding:4px 8px;border:2px solid var(--navy);border-radius:6px;background:var(--pin-white);cursor:pointer;">Apply</button>
                            </form>
                            <form method="POST" action="{{ route('manager.inventory.restock', $item) }}">
                                @csrf
                                <button type="submit" class="btn-lane primary" style="font-size:0.55rem;padding:4px 10px;">Restock</button>
                            </form>
                            <a href="{{ route('manager.inventory.edit', $item) }}" class="btn-lane secondary" style="font-size:0.55rem;padding:4px 10px;">Edit</a>
                            <form method="POST" action="{{ route('manager.inventory.destroy', $item) }}" onsubmit="return confirm('Remove {{ $item->name }}?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-lane danger" style="font-size:0.55rem;padding:4px 10px;">×</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center;padding:2rem;background:var(--sky-light);border:2px solid var(--navy);border-radius:10px;">
                        <span style="font-family:var(--font-mono);font-size:0.7rem;color:var(--slate);">No inventory items yet.</span>
                    </div>
                @endforelse
            </div>

        </div>
    </div>

    <x-toast />

    @include('sim.partials.fold-controls')
    @include('sim.partials.responsive')
</x-app-layout>
