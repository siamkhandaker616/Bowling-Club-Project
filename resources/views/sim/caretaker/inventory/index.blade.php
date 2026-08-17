<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Inventory</h2>

        </div>
    </x-slot>

    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div style="display:grid;grid-template-columns:180px 1fr 220px;gap:0;">
        <div style="background:var(--sky-light);border-right:3px solid var(--navy);padding:1rem;display:flex;flex-direction:column;">
            <div class="dash-section-label">Stock Alerts</div>
            <div style="display:flex;flex-direction:column;gap:6px;margin-top:8px;">
                @forelse ($lowStock as $item)
                    <div style="padding:8px;border-radius:8px;background:var(--pin-white);border:2px solid var(--coral);display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-family:var(--font-sub);font-size:0.65rem;color:var(--navy);">{{ $item->name }}</span>
                        <span class="pin knocked" style="color:var(--coral-dark);font-size:0.75rem;" title="Low stock">&#9679;</span>
                    </div>
                @empty
                    <div style="padding:8px;border-radius:8px;background:var(--pin-white);border:1px solid var(--fog);">
                        <div style="font-family:var(--font-sub);font-size:0.65rem;color:var(--sky-dark);">All stocked</div>
                    </div>
                @endforelse
                <div style="margin-top:auto;padding-top:8px;border-top:2px solid var(--fog);text-align:center;">
                    <div class="ball-avatar caretaker" style="width:48px;height:48px;border-radius:50%;background:var(--navy);color:var(--pin-white);display:inline-flex;align-items:center;justify-content:center;font-family:var(--font-header);font-size:1.1rem;font-weight:700;">CK</div>
                    <div style="font-family:var(--font-sub);font-size:0.65rem;color:var(--navy);margin-top:4px;font-weight:700;">Caretaker</div>
                </div>
            </div>
        </div>
        <div style="padding:1.25rem;overflow:hidden;">
            <div class="dash-section-label">All Stock</div>
            <div style="display:flex;flex-direction:column;gap:10px;margin-top:8px;">
                @forelse ($items as $item)
                    @php
                        $pct = $item->max_quantity > 0 ? (int) round(min(100, $item->quantity / $item->max_quantity * 100)) : 0;
                        $full = $item->quantity >= $item->max_quantity;
                    @endphp
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;display:flex;justify-content:space-between;align-items:center;gap:14px;">
                        <div style="flex:1;min-width:0;display:flex;gap:10px;align-items:center;">
                            @if ($item->isLowStock())
                                <span class="pin knocked" style="color:var(--coral-dark);font-size:0.75rem;" title="Low stock">&#9679;</span>
                            @else
                                <span class="pin standing" style="color:var(--ok);font-size:0.75rem;" title="OK">&#9679;</span>
                            @endif
                            <div style="min-width:0;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span class="badge-role" style="background:var(--mist);color:var(--slate);border:1px solid var(--fog);font-family:var(--font-mono);font-size:0.52rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;">{{ ucfirst($item->category) }}</span>
                                    <span style="font-family:var(--font-sub);font-size:0.75rem;">{{ $item->name }}</span>
                                </div>
                                <div style="display:flex;align-items:center;gap:8px;margin-top:6px;">
                                    <div style="width:140px;height:10px;border:2px solid var(--navy);border-radius:6px;background:var(--pin-white);overflow:hidden;">
                                        <div style="height:100%;width:{{ $pct }}%;background:{{ $item->isLowStock() ? 'var(--coral)' : 'var(--ok)' }};transition:width .3s ease;"></div>
                                    </div>
                                    <span style="font-family:var(--font-mono);font-size:0.62rem;font-weight:700;color:{{ $item->isLowStock() ? 'var(--coral-dark)' : 'var(--navy)' }};">{{ $item->quantity }} / {{ $item->max_quantity }}</span>
                                </div>
                                <span style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);">Restock at {{ $item->reorder_threshold }}</span>
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <form method="POST" action="{{ route('caretaker.inventory.adjust', $item) }}" class="gutter-form" style="display:flex;align-items:center;gap:6px;">
                                @csrf
                                <div class="stepper">
                                    <button type="button" data-stepper="edit" data-dir="-1">-</button>
                                    <input name="change" type="number" step="1" value="0" min="{{ 0 - $item->quantity }}" max="{{ $item->max_quantity - $item->quantity }}" data-stepper="edit" class="input" style="width:64px;text-align:center;">
                                    <button type="button" data-stepper="edit" data-dir="1">+</button>
                                </div>
                                <button type="submit" class="btn-lane primary" style="font-size:0.55rem;padding:4px 10px;" title="Log usage or restock">Adjust</button>
                            </form>
                            <form method="POST" action="{{ route('caretaker.inventory.restock', $item) }}">
                                @csrf
                                <button type="submit" class="btn-lane solid" style="font-size:0.55rem;padding:4px 10px;" title="Top up stock" @if ($full) disabled @endif>Restock</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;text-align:center;">
                        <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">No inventory items.</span>
                    </div>
                @endforelse
            </div>
        </div>
        <div style="background:var(--sky-light);border-left:3px solid var(--navy);padding:1rem;display:flex;flex-direction:column;">
            <div class="dash-section-label">Recent Activity</div>
            <div style="display:flex;flex-direction:column;gap:6px;margin-top:8px;">
                @forelse ($recentEvents as $event)
                    <div style="padding:6px 8px;background:var(--pin-white);border:1px solid var(--fog);border-radius:6px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:6px;">
                            <span style="font-family:var(--font-sub);font-size:0.62rem;color:var(--navy);">{{ $event->inventory->name ?? 'Item' }}</span>
                            <span style="font-family:var(--font-mono);font-size:0.62rem;font-weight:700;color:{{ $event->quantity_change > 0 ? 'var(--sky-dark)' : 'var(--coral-dark)' }};">{{ $event->quantity_change > 0 ? '+' : '' }}{{ $event->quantity_change }}</span>
                        </div>
                        <div style="font-family:var(--font-mono);font-size:0.5rem;color:var(--slate);margin-top:2px;">{{ $event->staff->user->name ?? 'System' }} · {{ $event->date }}</div>
                    </div>
                @empty
                    <div style="padding:8px;border-radius:8px;background:var(--pin-white);border:1px dashed var(--fog);">
                        <div style="font-family:var(--font-sub);font-size:0.62rem;color:var(--slate);">No activity yet — adjust stock to start the trail.</div>
                    </div>
                @endforelse
            </div>
            <div class="dash-section-label" style="margin-top:16px;">Summary</div>
            <div class="dash-stat" style="margin-top:8px;">
                <span class="dash-stat-num">{{ $items->count() }}</span>
                <span class="dash-stat-label">Total Items</span>
            </div>
            <div class="dash-stat" style="margin-top:6px;">
                <span class="dash-stat-num" style="color:var(--coral-dark);">{{ $lowStock->count() }}</span>
                <span class="dash-stat-label">Low Stock</span>
            </div>
            <div class="dash-section-label" style="margin-top:16px;">How it works</div>
            <p style="font-family:var(--font-body);font-size:0.6rem;color:var(--slate);margin:6px 0 0;line-height:1.5;">Type a &#43; or &#8722; change and hit Adjust. Restock refills to max.</p>
        </div>
    </div>
    </div>

    <x-toast />

    @include('sim.partials.responsive')
    @include('sim.partials.fold-controls')

    <style>
        .btn-lane[disabled]{opacity:.45;cursor:not-allowed}
    </style>
</x-app-layout>
