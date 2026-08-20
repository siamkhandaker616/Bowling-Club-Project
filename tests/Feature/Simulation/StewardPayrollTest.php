<?php

namespace Tests\Feature\Simulation;

use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class StewardPayrollTest extends TestCase
{
    use CreatesSimFixtures;

    public function test_steward_can_view_payroll_desk(): void
    {
        $this->clubConfig();
        $stewardUser = $this->makeUser(['role' => 'steward']);
        $this->makeStaff(['role' => 'caretaker']);

        $this->actingAs($stewardUser)
            ->get(route('steward.payroll.index'))
            ->assertOk()
            ->assertSee('Salary Desk');
    }

    public function test_steward_can_adjust_a_salary(): void
    {
        $this->clubConfig();
        $stewardUser = $this->makeUser(['role' => 'steward']);
        $staff = $this->makeStaff(['role' => 'caretaker', 'base_salary' => 3000, 'current_salary' => 2400]);

        $response = $this->actingAs($stewardUser)
            ->postJson(route('steward.payroll.update', $staff), ['salary' => 3300]);

        $response->assertOk()->assertJson(['ok' => true, 'salary' => 3300.0]);
        $this->assertSame(3300.0, (float) $staff->fresh()->current_salary);
    }

    public function test_salary_is_capped_at_one_and_half_times_base(): void
    {
        $this->clubConfig();
        $stewardUser = $this->makeUser(['role' => 'steward']);
        $staff = $this->makeStaff(['role' => 'caretaker', 'base_salary' => 2000, 'current_salary' => 2000]);

        $response = $this->actingAs($stewardUser)
            ->postJson(route('steward.payroll.update', $staff), ['salary' => 99999]);

        $response->assertOk()->assertJson(['ok' => true, 'capped' => true]);
        $this->assertSame(3000.0, (float) $staff->fresh()->current_salary);
    }

    public function test_restoring_docked_pay_clears_the_cut(): void
    {
        $this->clubConfig();
        $stewardUser = $this->makeUser(['role' => 'steward']);
        $staff = $this->makeStaff(['role' => 'caretaker', 'base_salary' => 2500, 'current_salary' => 1800]);

        $this->actingAs($stewardUser)
            ->postJson(route('steward.payroll.update', $staff), ['salary' => 2500])
            ->assertOk();

        $fresh = $staff->fresh();
        $this->assertSame(2500.0, (float) $fresh->current_salary);
        $this->assertTrue((float) $fresh->current_salary >= (float) $fresh->base_salary);
    }

    public function test_manager_cannot_use_the_steward_payroll_desk(): void
    {
        $this->clubConfig();
        $manager = $this->makeUser(['role' => 'admin']);
        $staff = $this->makeStaff(['role' => 'caretaker']);

        $this->actingAs($manager)
            ->get(route('steward.payroll.index'))
            ->assertForbidden();

        $this->actingAs($manager)
            ->postJson(route('steward.payroll.update', $staff), ['salary' => 1])
            ->assertForbidden();
    }
}
