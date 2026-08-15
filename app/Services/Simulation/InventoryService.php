<?php

namespace App\Services\Simulation;

use App\Models\ClubConfig;
use App\Models\Inventory;
use App\Models\InventoryEvent;
use App\Models\Lane;
use Carbon\Carbon;

class InventoryService
{
    public function store(array $data): Inventory
    {
        return Inventory::create($data);
    }

    public function update(Inventory $inventory, array $data): Inventory
    {
        $inventory->update($data);

        return $inventory;
    }

    public function adjust(Inventory $inventory, int $change, string $eventType, string $reason, ?int $staffId = null): Inventory
    {
        $inventory->quantity = max(0, min($inventory->max_quantity, $inventory->quantity + $change));
        $inventory->save();

        InventoryEvent::create([
            'inventory_id' => $inventory->id,
            'staff_id' => $staffId,
            'event_type' => $eventType,
            'quantity_change' => $change,
            'description' => $reason,
            'date' => Clock::date(),
        ]);

        return $inventory;
    }

    public function restock(Inventory $inventory, ?int $staffId = null): Inventory
    {
        $qty = $inventory->max_quantity - $inventory->quantity;
        if ($qty <= 0) {
            return $inventory;
        }

        $cost = $qty * (float) $inventory->cost_per_unit;

        $this->adjust($inventory, $qty, 'restock', 'Full restock to max capacity', $staffId);
        $inventory->last_restocked_at = now();
        $inventory->save();

        $cfg = ClubConfig::singleton();
        $cfg->total_expenses = $cfg->total_expenses + $cost;
        $cfg->save();

        return $inventory;
    }

    public function dailyDecay(array &$log): void
    {
        $lanes = Lane::count();

        $decays = [
            'Lane Oil' => -ceil($lanes * 0.5),
            'Cleaning Wipes' => -3,
            'Bar Napkins' => -5,
        ];

        foreach ($decays as $name => $change) {
            $item = Inventory::where('name', $name)->first();
            if (! $item) {
                continue;
            }

            $this->adjust($item, $change, 'usage', 'Daily wear and tear', null);
        }

        foreach (Inventory::all() as $item) {
            if ($item->isLowStock()) {
                $log['low_stock']->push([
                    'id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'reorder_threshold' => $item->reorder_threshold,
                ]);
            }
        }
    }
}
