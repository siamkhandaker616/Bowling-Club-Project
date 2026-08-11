<?php

namespace App\Http\Controllers\Sim\Caretaker;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Services\Simulation\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(private InventoryService $service)
    {
    }

    public function index()
    {
        $items = Inventory::orderBy('category')->orderBy('name')->get();
        $lowStock = $items->filter(fn ($i) => $i->isLowStock());

        return view('sim.caretaker.inventory.index', compact('items', 'lowStock'));
    }

    public function adjust(Request $request, Inventory $inventory)
    {
        $data = $request->validate([
            'change' => ['required', 'integer', 'between:-9999,9999'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->service->adjust(
            $inventory,
            $data['change'],
            $data['change'] > 0 ? 'restock' : 'usage',
            $data['reason'] ?? 'Caretaker adjustment',
            $request->user()->staff->id,
        );

        session()->flash('success', $inventory->name . ' adjusted by ' . $data['change'] . '.');

        return redirect()->route('caretaker.inventory.index');
    }
}
