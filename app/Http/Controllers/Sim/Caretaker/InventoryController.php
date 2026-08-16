<?php

namespace App\Http\Controllers\Sim\Caretaker;

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
        $items = Inventory::orderBy('category')->orderBy('name')->get();
        $lowStock = $items->filter(fn ($i) => $i->isLowStock());
        $recentEvents = InventoryEvent::with('inventory', 'staff.user')->latest('created_at')->limit(5)->get();

        return view('sim.caretaker.inventory.index', compact('items', 'lowStock', 'recentEvents'));
    }

    public function adjust(Request $request, Inventory $inventory)
    {
        $staff = $request->user()->staff;
        if (! $staff || ! $staff->is_active) {
            abort(403);
        }

        $data = $request->validate([
            'change' => ['required', 'integer', 'between:-9999,9999'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        if ($inventory->quantity + $data['change'] < 0) {
            session()->flash('error', $inventory->name . ' only has ' . $inventory->quantity . ' — you can\'t take more than the shelf holds.');

            return redirect()->back();
        }

        if ($inventory->quantity + $data['change'] > $inventory->max_quantity) {
            session()->flash('error', $inventory->name . ' caps at ' . $inventory->max_quantity . ' — use Restock to fill it to max.');

            return redirect()->back();
        }

        $before = $inventory->quantity;

        $this->service->adjust(
            $inventory,
            $data['change'],
            $data['change'] > 0 ? 'restock' : 'usage',
            $data['reason'] ?? 'Caretaker adjustment',
            $staff->id,
        );

        $added = $inventory->quantity - $before;

        if ($added > 0) {
            $bill = $this->billService->createPending($inventory, $added, $staff->id);

            session()->flash('success', $inventory->name . ' adjusted by ' . $data['change'] . '. Now at ' . $inventory->quantity . '. Purchase bill (' . $bill->quantity . ' units, $' . number_format((float) $bill->total, 2) . ') sent to the manager for approval.');

            return redirect()->route('caretaker.inventory.index');
        }

        session()->flash('success', $inventory->name . ' adjusted by ' . $data['change'] . '. Now at ' . $inventory->quantity . '.');

        return redirect()->route('caretaker.inventory.index');
    }

    public function restock(Request $request, Inventory $inventory)
    {
        $staff = $request->user()->staff;
        if (! $staff || ! $staff->is_active) {
            abort(403);
        }

        $before = $inventory->quantity;

        $this->service->restock($inventory, $staff->id);

        $added = $inventory->quantity - $before;

        if ($added > 0) {
            $bill = $this->billService->createPending($inventory, $added, $staff->id);

            session()->flash('success', $inventory->name . ' restocked to max (' . $before . ' -> ' . $inventory->quantity . '). Bill ($' . number_format((float) $bill->total, 2) . ') is pending manager approval.');

            return redirect()->route('caretaker.inventory.index');
        }

        session()->flash('success', $inventory->name . ' restocked to max (' . $before . ' -> ' . $inventory->quantity . ').');

        return redirect()->route('caretaker.inventory.index');
    }
}
