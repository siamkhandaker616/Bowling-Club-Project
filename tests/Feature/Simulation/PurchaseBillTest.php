<?php

namespace Tests\Feature\Simulation;

use App\Models\ClubConfig;
use App\Models\Inventory;
use App\Models\InventoryPurchase;
use App\Models\Payment;
use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class PurchaseBillTest extends TestCase
{
    use CreatesSimFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clubConfig(['total_expenses' => 0]);
        config([
            'services.sslcommerz.store_id' => '',
            'services.sslcommerz.store_password' => '',
        ]);
    }

    private function manager()
    {
        return $this->makeStaff(['role' => 'club_manager'], ['role' => 'admin'])->user;
    }

    private function caretaker()
    {
        return $this->makeStaff(['role' => 'caretaker'])->user;
    }

    private function pendingBill(): InventoryPurchase
    {
        $item = $this->makeInventory(['quantity' => 4, 'max_quantity' => 20, 'cost_per_unit' => 2.5]);

        $this->actingAs($this->caretaker())
            ->post(route('caretaker.inventory.restock', $item));

        return InventoryPurchase::where('inventory_id', $item->id)->firstOrFail();
    }

    public function test_caretaker_positive_adjust_creates_pending_bill_without_charging(): void
    {
        $caretaker = $this->caretaker();
        $item = $this->makeInventory(['quantity' => 10, 'max_quantity' => 20, 'cost_per_unit' => 2.5]);

        $this->actingAs($caretaker)
            ->post(route('caretaker.inventory.adjust', $item), ['change' => 3])
            ->assertRedirect(route('caretaker.inventory.index'));

        $this->assertSame(13, $item->fresh()->quantity);
        $this->assertSame(0.0, (float) ClubConfig::first()->total_expenses);
        $this->assertDatabaseHas('inventory_purchases', [
            'inventory_id' => $item->id,
            'quantity' => 3,
            'total' => 7.5,
            'status' => 'pending',
            'requested_by' => $caretaker->staff->id,
        ]);
    }

    public function test_caretaker_negative_adjust_creates_no_bill(): void
    {
        $caretaker = $this->caretaker();
        $item = $this->makeInventory();

        $this->actingAs($caretaker)
            ->post(route('caretaker.inventory.adjust', $item), ['change' => -2])
            ->assertRedirect(route('caretaker.inventory.index'));

        $this->assertSame(2, $item->fresh()->quantity);
        $this->assertSame(0, InventoryPurchase::count());
        $this->assertSame(0.0, (float) ClubConfig::first()->total_expenses);
    }

    public function test_manager_restock_auto_approves_and_charges_immediately(): void
    {
        $item = $this->makeInventory(['quantity' => 4, 'max_quantity' => 20, 'cost_per_unit' => 2.5]);
        $manager = $this->manager();

        $this->actingAs($manager)
            ->post(route('manager.inventory.restock', $item))
            ->assertRedirect(route('manager.inventory.index'));

        $this->assertSame(20, $item->fresh()->quantity);
        $this->assertSame(40.0, (float) ClubConfig::first()->total_expenses);
        $this->assertDatabaseHas('inventory_purchases', [
            'inventory_id' => $item->id,
            'quantity' => 16,
            'total' => 40.0,
            'status' => 'approved',
            'auto_approved' => true,
            'reviewed_by' => $manager->staff->id,
        ]);
        $this->assertSame(1, Payment::where('payable_type', InventoryPurchase::class)->count());
        $this->assertSame(1, Payment::where('status', 'success')->count());
    }

    public function test_manager_positive_adjust_auto_approves_and_charges(): void
    {
        $item = $this->makeInventory(['quantity' => 10, 'max_quantity' => 20, 'cost_per_unit' => 2.5]);

        $this->actingAs($this->manager())
            ->post(route('manager.inventory.adjust', $item), ['change' => 3])
            ->assertRedirect(route('manager.inventory.index'));

        $this->assertSame(13, $item->fresh()->quantity);
        $this->assertSame(7.5, (float) ClubConfig::first()->total_expenses);
        $this->assertDatabaseHas('inventory_purchases', [
            'inventory_id' => $item->id,
            'quantity' => 3,
            'status' => 'approved',
            'auto_approved' => true,
        ]);
    }

    public function test_manager_accept_pending_bill_simulates_payment_and_charges(): void
    {
        $bill = $this->pendingBill();
        $manager = $this->manager();

        $this->actingAs($manager)
            ->post(route('manager.inventory.purchases.accept', $bill))
            ->assertRedirect(route('manager.inventory.purchases.index'));

        $this->assertSame('approved', $bill->fresh()->status);
        $this->assertSame(40.0, (float) ClubConfig::first()->total_expenses);
        $this->assertSame($manager->staff->id, $bill->fresh()->reviewed_by);
        $this->assertNotNull($bill->fresh()->payment_id);

        $payment = $bill->fresh()->payment;
        $this->assertTrue($payment->isSuccessful());
        $this->assertSame(InventoryPurchase::class, $payment->payable_type);
    }

    public function test_manager_accept_is_idempotent_after_approval(): void
    {
        $bill = $this->pendingBill();

        $this->actingAs($this->manager())
            ->post(route('manager.inventory.purchases.accept', $bill))
            ->assertRedirect(route('manager.inventory.purchases.index'));

        $count = Payment::where('payable_type', InventoryPurchase::class)->count();

        $this->actingAs($this->manager())
            ->post(route('manager.inventory.purchases.accept', $bill))
            ->assertStatus(409);

        $this->assertSame($count, Payment::where('payable_type', InventoryPurchase::class)->count());
    }

    public function test_manager_reject_pending_bill_returns_stock_without_fine(): void
    {
        $bill = $this->pendingBill();
        $item = $bill->inventory;

        $this->actingAs($this->manager())
            ->post(route('manager.inventory.purchases.reject', $bill))
            ->assertRedirect(route('manager.inventory.purchases.index'));

        $this->assertSame('rejected', $bill->fresh()->status);
        $this->assertSame(4, $item->fresh()->quantity);
        $this->assertNull($bill->fresh()->fine_amount);
        $this->assertSame(0.0, (float) ClubConfig::first()->total_expenses);
    }

    public function test_manager_reject_bill_with_partially_consumed_stock_fines_manager(): void
    {
        $bill = $this->pendingBill();
        $item = $bill->inventory;

        $this->actingAs($this->caretaker())
            ->post(route('caretaker.inventory.adjust', $item), ['change' => -6]);

        $this->actingAs($this->manager())
            ->post(route('manager.inventory.purchases.reject', $bill))
            ->assertRedirect(route('manager.inventory.purchases.index'));

        $this->assertSame('rejected', $bill->fresh()->status);
        $this->assertSame(0, $item->fresh()->quantity);
        $this->assertSame(5.0, (float) $bill->fresh()->fine_amount);
        $this->assertSame(5.0, (float) ClubConfig::first()->total_expenses);
    }

    public function test_manager_reject_fully_consumed_bill_fines_full_value(): void
    {
        $bill = $this->pendingBill();
        $item = $bill->inventory;

        $this->actingAs($this->caretaker())
            ->post(route('caretaker.inventory.adjust', $item), ['change' => -16]);

        $this->actingAs($this->manager())
            ->post(route('manager.inventory.purchases.reject', $bill))
            ->assertRedirect(route('manager.inventory.purchases.index'));

        $this->assertSame(0, $item->fresh()->quantity);
        $this->assertSame(30.0, (float) $bill->fresh()->fine_amount);
        $this->assertSame(30.0, (float) ClubConfig::first()->total_expenses);
    }

    public function test_manager_cannot_reject_an_approved_bill(): void
    {
        $bill = $this->pendingBill();

        $this->actingAs($this->manager())
            ->post(route('manager.inventory.purchases.accept', $bill));

        $this->actingAs($this->manager())
            ->post(route('manager.inventory.purchases.reject', $bill))
            ->assertStatus(409);
    }

    public function test_caretaker_cannot_manage_purchase_bills(): void
    {
        $bill = $this->pendingBill();

        $this->actingAs($this->caretaker())
            ->post(route('manager.inventory.purchases.accept', $bill))
            ->assertForbidden();

        $this->actingAs($this->caretaker())
            ->post(route('manager.inventory.purchases.reject', $bill))
            ->assertForbidden();
    }

    public function test_bills_page_renders_ledger_and_actions(): void
    {
        $bill = $this->pendingBill();

        $this->actingAs($this->manager())
            ->get(route('manager.inventory.purchases.index'))
            ->assertOk()
            ->assertSee('Purchase Bills')
            ->assertSee($bill->item_name)
            ->assertSee('Accept', false)
            ->assertSee('Reject', false);
    }

    public function test_inventory_page_no_longer_hosts_the_bills_ledger(): void
    {
        $this->pendingBill();

        $this->actingAs($this->manager())
            ->get(route('manager.inventory.index'))
            ->assertOk()
            ->assertDontSee('Bill Ledger')
            ->assertDontSee('Accept &amp; Pay', false);
    }

    public function test_bill_snapshot_survives_later_item_edits(): void
    {
        $bill = $this->pendingBill();
        $item = $bill->inventory;

        $item->update(['name' => 'Renamed', 'cost_per_unit' => 99]);

        $this->assertSame('pending', $bill->fresh()->status);
        $this->assertSame($bill->item_name, $bill->fresh()->item_name);
        $this->assertSame(2.5, (float) $bill->fresh()->unit_cost);
    }
}
