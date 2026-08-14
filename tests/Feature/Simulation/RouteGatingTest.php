<?php

namespace Tests\Feature\Simulation;

use Illuminate\Support\Facades\Route;
use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class RouteGatingTest extends TestCase
{
    use CreatesSimFixtures;

    public function test_simulation_routes_are_registered(): void
    {
        $names = [
            'manager.dashboard',
            'manager.staff.index',
            'manager.staff.bonus',
            'manager.inventory.restock',
            'manager.bookings.cancel',
            'manager.complaints.resolve',
            'manager.confrontations.respond',
            'manager.day.stats',
            'manager.day.advance',
            'manager.day.toggleBadDay',
            'caretaker.shifts.index',
            'caretaker.crew.vent',
            'caretaker.lanes.maintain',
            'steward.schedule.index',
            'visitor.bookings.cancel',
            'reapply.index',
            'reapply.store',
        ];

        foreach ($names as $name) {
            $this->assertTrue(Route::has($name), "Route {$name} should be registered.");
        }
    }

    public function test_admin_can_read_day_stats_but_other_roles_cannot(): void
    {
        $admin = $this->makeUser(['role' => 'admin']);
        $caretaker = $this->makeUser(['role' => 'caretaker']);
        $steward = $this->makeUser(['role' => 'steward']);
        $customer = $this->makeUser(['role' => 'customer']);

        $this->actingAs($admin)->get(route('manager.day.stats'))->assertOk();
        $this->actingAs($caretaker)->get(route('manager.day.stats'))->assertForbidden();
        $this->actingAs($steward)->get(route('manager.day.stats'))->assertForbidden();
        $this->actingAs($customer)->get(route('manager.day.stats'))->assertForbidden();
    }

    public function test_only_admin_can_toggle_bad_day(): void
    {
        $admin = $this->makeUser(['role' => 'admin']);
        $caretaker = $this->makeUser(['role' => 'caretaker']);

        $this->actingAs($caretaker)->post(route('manager.day.toggleBadDay'))->assertForbidden();
        $this->actingAs($admin)->post(route('manager.day.toggleBadDay'))->assertRedirect();
    }

    public function test_only_caretakers_with_staff_can_vent(): void
    {
        $caretaker = $this->makeStaff()->user;
        $admin = $this->makeUser(['role' => 'admin']);
        $idleCaretaker = $this->makeUser(['role' => 'caretaker']);

        $this->actingAs($admin)->post(route('caretaker.crew.vent'))->assertForbidden();
        $this->actingAs($idleCaretaker)->post(route('caretaker.crew.vent'))->assertForbidden();
        $this->actingAs($caretaker)->post(route('caretaker.crew.vent'))->assertRedirect();
    }
}
