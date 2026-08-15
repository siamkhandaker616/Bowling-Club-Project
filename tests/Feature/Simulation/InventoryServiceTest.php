<?php

namespace Tests\Feature\Simulation;

use App\Models\ClubConfig;
use App\Models\Inventory;
use App\Services\Simulation\InventoryService;
use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use CreatesSimFixtures;

    public function test_daily_decay_consumes_lane_oil_and_reports_low_stock(): void
    {
        $this->clubConfig();
        $this->makeLane();
        $oil = Inventory::create([
            'name' => 'Lane Oil',
            'category' => 'oil_supplies',
            'quantity' => 2,
            'max_quantity' => 20,
            'reorder_threshold' => 5,
            'cost_per_unit' => 10,
        ]);

        $log = $this->simLog();
        app(InventoryService::class)->dailyDecay($log);

        $this->assertSame(1, $oil->fresh()->quantity);
        $this->assertCount(1, $log['low_stock']);
        $this->assertSame('Lane Oil', $log['low_stock']->first()['name']);
        $this->assertDatabaseHas('inventory_events', [
            'inventory_id' => $oil->id,
            'event_type' => 'usage',
            'quantity_change' => -1,
        ]);
    }

    public function test_restock_refills_to_max_and_charges_expenses(): void
    {
        $this->clubConfig(['total_expenses' => 0]);
        $item = Inventory::create([
            'name' => 'Cleaning Wipes',
            'category' => 'cleaning_supplies',
            'quantity' => 4,
            'max_quantity' => 20,
            'reorder_threshold' => 5,
            'cost_per_unit' => 3.5,
        ]);

        app(InventoryService::class)->restock($item);

        $this->assertSame(20, $item->fresh()->quantity);
        $this->assertSame(56.0, (float) ClubConfig::first()->total_expenses);
        $this->assertDatabaseHas('inventory_events', [
            'inventory_id' => $item->id,
            'event_type' => 'restock',
        ]);
    }
}
