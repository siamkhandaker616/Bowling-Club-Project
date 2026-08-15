<?php

namespace Tests\Feature\Simulation;

use App\Models\ClubConfig;
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

    public function test_caretaker_restock_fills_item_to_max_and_charges_expenses(): void
    {
        $caretaker = $this->makeStaff()->user;
        $item = $this->makeInventory(['quantity' => 4, 'max_quantity' => 20, 'cost_per_unit' => 2.5]);

        $this->actingAs($caretaker)
            ->post(route('caretaker.inventory.restock', $item))
            ->assertRedirect(route('caretaker.inventory.index'));

        $this->assertSame(20, $item->fresh()->quantity);
        $this->assertSame(40.0, (float) ClubConfig::first()->total_expenses);
    }

    public function test_caretaker_adjust_clamps_at_max_quantity(): void
    {
        $caretaker = $this->makeStaff()->user;
        $item = $this->makeInventory(['quantity' => 18, 'max_quantity' => 20]);

        $this->actingAs($caretaker)
            ->post(route('caretaker.inventory.adjust', $item), ['change' => 5])
            ->assertRedirect(route('caretaker.inventory.index'));

        $this->assertSame(20, $item->fresh()->quantity);
    }
}
