<?php

namespace Tests\Feature\Simulation;

use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class LaneMaintenanceTest extends TestCase
{
    use CreatesSimFixtures;

    public function test_oiling_a_lane_refills_oil_and_logs_maintenance(): void
    {
        $caretaker = $this->makeStaff()->user;
        $lane = $this->makeLane(['oil_level' => 30]);

        $this->actingAs($caretaker)
            ->post(route('caretaker.lanes.maintain', $lane), ['action' => 'oiled'])
            ->assertRedirect();

        $lane->refresh();
        $this->assertSame(100, $lane->oil_level);
        $this->assertNotNull($lane->last_maintained_at);
    }

    public function test_cleaning_a_lane_logs_maintenance_without_touching_oil(): void
    {
        $caretaker = $this->makeStaff()->user;
        $lane = $this->makeLane(['oil_level' => 40]);

        $this->actingAs($caretaker)
            ->post(route('caretaker.lanes.maintain', $lane), ['action' => 'cleaned'])
            ->assertRedirect();

        $lane->refresh();
        $this->assertSame(40, $lane->oil_level);
        $this->assertNotNull($lane->last_maintained_at);
    }

    public function test_toggling_maintenance_flips_status_between_open_and_maintenance(): void
    {
        $caretaker = $this->makeStaff()->user;
        $lane = $this->makeLane();

        $this->actingAs($caretaker)
            ->post(route('caretaker.lanes.maintain', $lane), ['action' => 'toggle_maint'])
            ->assertRedirect();

        $this->assertSame('maintenance', $lane->fresh()->status);

        $this->actingAs($caretaker)
            ->post(route('caretaker.lanes.maintain', $lane), ['action' => 'toggle_maint'])
            ->assertRedirect();

        $this->assertSame('open', $lane->fresh()->status);
    }

    public function test_occupied_lane_cannot_be_flagged_maintenance(): void
    {
        $caretaker = $this->makeStaff()->user;
        $lane = $this->makeLane(['status' => 'occupied']);

        $this->actingAs($caretaker)
            ->post(route('caretaker.lanes.maintain', $lane), ['action' => 'toggle_maint'])
            ->assertRedirect();

        $this->assertSame('occupied', $lane->fresh()->status);
    }

    public function test_admin_can_maintain_but_customer_cannot(): void
    {
        $admin = $this->makeUser(['role' => 'admin']);
        $customer = $this->makeUser(['role' => 'customer']);
        $lane = $this->makeLane();

        $this->actingAs($admin)->post(route('caretaker.lanes.maintain', $lane), ['action' => 'oiled'])->assertRedirect();
        $this->actingAs($customer)->post(route('caretaker.lanes.maintain', $lane), ['action' => 'oiled'])->assertForbidden();
    }

    public function test_invalid_action_is_rejected(): void
    {
        $caretaker = $this->makeStaff()->user;
        $lane = $this->makeLane();

        $this->actingAs($caretaker)
            ->post(route('caretaker.lanes.maintain', $lane), ['action' => 'explode'])
            ->assertSessionHasErrors('action');
    }
}
