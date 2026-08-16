<?php

namespace Tests\Feature\Simulation;

use App\Models\ClubConfig;
use App\Models\InventoryPurchase;
use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class CaretakerInventoryTest extends TestCase
{
    use CreatesSimFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clubConfig(['total_expenses' => 0]);
    }

    public function test_inventory_page_renders_interactive_stock_controls(): void
    {
        $caretaker = $this->makeStaff()->user;
        $this->makeInventory(['name' => 'Lane Oil', 'quantity' => 4, 'max_quantity' => 20, 'reorder_threshold' => 5]);

        $this->actingAs($caretaker)
            ->get(route('caretaker.inventory.index'))
            ->assertOk()
            ->assertSee('Lane Oil')
            ->assertSee('4 / 20')
            ->assertSee('reorder @ 5')
            ->assertSee('Adjust')
            ->assertSee('Restock');
    }

    public function test_inventory_page_shows_recent_activity_after_adjust(): void
    {
        $caretaker = $this->makeStaff()->user;
        $item = $this->makeInventory(['name' => 'Lane Oil']);

        $this->actingAs($caretaker)
            ->post(route('caretaker.inventory.adjust', $item), ['change' => -2])
            ->assertRedirect(route('caretaker.inventory.index'));

        $this->actingAs($caretaker)
            ->get(route('caretaker.inventory.index'))
            ->assertOk()
            ->assertSee('Recent Activity')
            ->assertSee('Lane Oil')
            ->assertSee('-2');
    }

    public function test_caretaker_restock_fills_item_to_max_and_creates_pending_bill(): void
    {
        $caretaker = $this->makeStaff()->user;
        $item = $this->makeInventory(['quantity' => 4, 'max_quantity' => 20, 'cost_per_unit' => 2.5]);

        $this->actingAs($caretaker)
            ->post(route('caretaker.inventory.restock', $item))
            ->assertRedirect(route('caretaker.inventory.index'));

        $this->assertSame(20, $item->fresh()->quantity);
        $this->assertSame(0.0, (float) ClubConfig::first()->total_expenses);
        $this->assertDatabaseHas('inventory_purchases', [
            'inventory_id' => $item->id,
            'item_name' => $item->name,
            'quantity' => 16,
            'total' => 40.0,
            'status' => 'pending',
            'auto_approved' => false,
        ]);
    }

    public function test_caretaker_adjust_rejects_change_above_max(): void
    {
        $caretaker = $this->makeStaff()->user;
        $item = $this->makeInventory(['quantity' => 18, 'max_quantity' => 20]);

        $this->actingAs($caretaker)
            ->post(route('caretaker.inventory.adjust', $item), ['change' => 5])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(18, $item->fresh()->quantity);
    }

    public function test_caretaker_adjust_rejects_change_below_zero(): void
    {
        $caretaker = $this->makeStaff()->user;
        $item = $this->makeInventory(['quantity' => 4]);

        $this->actingAs($caretaker)
            ->post(route('caretaker.inventory.adjust', $item), ['change' => -10])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(4, $item->fresh()->quantity);
        $this->assertSame(0, InventoryPurchase::count());
    }
}
