<?php

namespace App\Http\Controllers\Sim\Manager;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryEvent;
use App\Services\Simulation\InventoryService;
use App\Services\Simulation\PurchaseBillService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(private InventoryService $service, private PurchaseBillService $billService)
    {
    }

    public function index()
    {
        $items = Inventory::withCount('events')->orderBy('category')->orderBy('name')->get();
        $lowStock = $items->filter(fn ($i) => $i->isLowStock());

        return view('sim.manager.inventory.index', compact('items', 'lowStock'));
    }

    public function create()
    {
        return view('sim.manager.inventory.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:0'],
            'max_quantity' => ['required', 'integer', 'min:1'],
            'reorder_threshold' => ['required', 'integer', 'min:0'],
            'cost_per_unit' => ['required', 'numeric', 'min:0'],
        ]);

        $item = $this->service->store($data);

        InventoryEvent::create([
            'inventory_id' => $item->id,
            'staff_id' => $request->user()->staff?->id,
            'event_type' => 'initial',
            'quantity_change' => $item->quantity,
            'description' => 'Item added to inventory',
            'date' => \App\Services\Simulation\Clock::date(),
        ]);

        session()->flash('success', $item->name . ' added.');

        return redirect()->route('manager.inventory.index');
    }

    public function edit(Inventory $inventory)
    {
        return view('sim.manager.inventory.edit', compact('inventory'));
    }

    public function update(Request $request, Inventory $inventory)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:0'],
            'max_quantity' => ['required', 'integer', 'min:1'],
            'reorder_threshold' => ['required', 'integer', 'min:0'],
            'cost_per_unit' => ['required', 'numeric', 'min:0'],
        ]);

        $this->service->update($inventory, $data);

        session()->flash('success', $inventory->name . ' updated.');

        return redirect()->route('manager.inventory.index');
    }

    public function restock(Request $request, Inventory $inventory)
    {
        $before = $inventory->quantity;

        $this->service->restock($inventory, $request->user()->staff?->id);

        $added = $inventory->quantity - $before;

        if ($added > 0) {
            $this->billService->autoApprove($inventory, $added, $request->user()->staff?->id);
        }

        session()->flash('success', $inventory->name . ' restocked to ' . $inventory->max_quantity . '.');

        return redirect()->route('manager.inventory.index');
    }

    public function adjust(Request $request, Inventory $inventory)
    {
        $data = $request->validate([
            'change' => ['required', 'integer', 'between:-9999,9999'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        if ($inventory->quantity + $data['change'] < 0) {
            session()->flash('error', $inventory->name . ' only has ' . $inventory->quantity . ' — can\'t go below zero.');

            return redirect()->back();
        }

        if ($inventory->quantity + $data['change'] > $inventory->max_quantity) {
            session()->flash('error', $inventory->name . ' caps at ' . $inventory->max_quantity . ' — use Restock to fill it to max.');

            return redirect()->back();
        }

        $before = $inventory->quantity;

        $this->service->adjust($inventory, $data['change'], $data['change'] > 0 ? 'restock' : 'usage', $data['reason'] ?? 'Manual adjustment', $request->user()->staff?->id);

        $added = $inventory->quantity - $before;

        if ($added > 0) {
            $this->billService->autoApprove($inventory, $added, $request->user()->staff?->id);
        }

        session()->flash('success', $inventory->name . ' adjusted by ' . $data['change'] . '.');

        return redirect()->route('manager.inventory.index');
    }

    public function destroy(Inventory $inventory)
    {
        $name = $inventory->name;
        $inventory->delete();

        session()->flash('success', $name . ' removed from inventory.');

        return redirect()->route('manager.inventory.index');
    }
}
