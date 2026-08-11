<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Inventory</h2>
            <span class="badge-role caretaker">Caretaker</span>
        </div>
    </x-slot>

    <div style="zoom:1.25;display:grid;grid-template-columns:180px 1fr 220px;gap:0;min-height:calc(100vh - 200px);">
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
            <div style="display:flex;flex-direction:column;gap:8px;margin-top:8px;">
                @forelse ($items as $item)
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;display:flex;justify-content:space-between;align-items:center;gap:10px;">
                        <div style="flex:1;display:flex;gap:10px;align-items:center;">
                            <span class="badge-role" style="background:var(--mist);color:var(--slate);border:1px solid var(--fog);font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;">{{ $item->category }}</span>
                            <span style="font-family:var(--font-sub);font-size:0.75rem;">{{ $item->name }}</span>
                            <span style="font-family:var(--font-mono);font-size:0.55rem;color:var(--slate);">reorder @ {{ $item->reorder_level }}</span>
                            @if ($item->isLowStock())
                                <span class="pin knocked" style="color:var(--coral-dark);font-size:0.75rem;" title="Low stock">&#9679;</span>
                            @else
                                <span class="pin standing" style="color:var(--sky-dark);font-size:0.75rem;" title="OK">&#9679;</span>
                            @endif
                        </div>
                        <div class="dash-stat-num" style="color:{{ $item->isLowStock() ? 'var(--coral-dark)' : 'var(--navy)' }};">{{ $item->quantity }}</div>
                        <form method="POST" action="{{ route('caretaker.inventory.adjust', $item) }}" style="display:flex;gap:6px;">
                            @csrf
                            <input name="change" type="number" step="1" placeholder="&#916;" style="width:70px;font-family:var(--font-body);font-size:0.7rem;padding:4px 8px;border:2px solid var(--navy);border-radius:6px;background:var(--pin-white);" title="Negative = usage, positive = restock">
                            <button type="submit" class="btn-lane primary" style="font-size:0.55rem;padding:4px 10px;">Adjust</button>
                        </form>
                    </div>
                @empty
                    <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;text-align:center;">
                        <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">No inventory items.</span>
                    </div>
                @endforelse
            </div>
        </div>
        <div style="background:var(--sky-light);border-left:3px solid var(--navy);padding:1rem;display:flex;flex-direction:column;">
            <div class="dash-section-label">Quick Actions</div>
            <div style="display:flex;flex-direction:column;gap:8px;margin-top:8px;">
                <a href="{{ route('caretaker.dashboard') }}" class="btn-lane secondary" style="display:block;text-align:center;text-decoration:none;font-size:0.65rem;padding:8px 12px;">Dashboard</a>
                <a href="{{ route('caretaker.shifts.index') }}" class="btn-lane secondary" style="display:block;text-align:center;text-decoration:none;font-size:0.65rem;padding:8px 12px;">Shifts</a>
                <a href="{{ route('caretaker.crew.index') }}" class="btn-lane secondary" style="display:block;text-align:center;text-decoration:none;font-size:0.65rem;padding:8px 12px;">Crew</a>
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
        </div>
    </div>
</x-app-layout>
