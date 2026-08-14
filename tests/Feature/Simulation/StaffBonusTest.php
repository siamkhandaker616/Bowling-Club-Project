<?php

namespace Tests\Feature\Simulation;

use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class StaffBonusTest extends TestCase
{
    use CreatesSimFixtures;

    public function test_recognition_bonus_lifts_happiness_by_ten(): void
    {
        $this->clubConfig();
        $admin = $this->makeUser(['role' => 'admin']);
        $staff = $this->makeStaff(['happiness' => 70]);

        $this->actingAs($admin)->post(route('manager.staff.bonus', $staff), [
            'type' => 'recognition',
            'amount_or_hours' => 0,
            'reason' => 'Great shift',
        ])->assertRedirect();

        $this->assertSame(80, $staff->fresh()->happiness);
        $this->assertDatabaseHas('bonuses', ['staff_id' => $staff->id, 'type' => 'recognition']);
        $this->assertDatabaseHas('staff_events', ['staff_id' => $staff->id, 'event_type' => 'bonus', 'happiness_change' => 10]);
    }

    public function test_cash_bonus_lifts_happiness_by_five(): void
    {
        $this->clubConfig();
        $admin = $this->makeUser(['role' => 'admin']);
        $staff = $this->makeStaff(['happiness' => 70]);

        $this->actingAs($admin)->post(route('manager.staff.bonus', $staff), [
            'type' => 'cash',
            'amount_or_hours' => 50,
            'reason' => 'Loyalty',
        ])->assertRedirect();

        $this->assertSame(75, $staff->fresh()->happiness);
    }

    public function test_time_off_bonus_lifts_happiness_by_three(): void
    {
        $this->clubConfig();
        $admin = $this->makeUser(['role' => 'admin']);
        $staff = $this->makeStaff(['happiness' => 70]);

        $this->actingAs($admin)->post(route('manager.staff.bonus', $staff), [
            'type' => 'time_off',
            'amount_or_hours' => 4,
            'reason' => 'Recovery',
        ])->assertRedirect();

        $this->assertSame(73, $staff->fresh()->happiness);
    }

    public function test_happiness_bonus_is_clamped_at_one_hundred(): void
    {
        $this->clubConfig();
        $admin = $this->makeUser(['role' => 'admin']);
        $staff = $this->makeStaff(['happiness' => 95]);

        $this->actingAs($admin)->post(route('manager.staff.bonus', $staff), [
            'type' => 'recognition',
            'amount_or_hours' => 0,
            'reason' => 'MVP',
        ])->assertRedirect();

        $this->assertSame(100, $staff->fresh()->happiness);
    }
}
