<?php

namespace Tests\Feature;

use App\Models\Staff;
use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class DebugCsrfTest extends TestCase
{
    use CreatesSimFixtures;

    public function test_check_running_unit_tests(): void
    {
        $this->assertTrue(app()->runningUnitTests(), 'runningUnitTests should be true');
        $this->assertTrue(app()->runningInConsole(), 'runningInConsole should be true');
    }

    public function test_simple_post(): void
    {
        $this->clubConfig();
        $admin = $this->makeUser(['role' => 'admin']);
        $staff = $this->makeStaff(['role' => 'club_manager']);

        $response = $this->actingAs($admin)->post(route('manager.staff.bonus', $staff), [
            'type' => 'recognition',
            'amount_or_hours' => 0,
            'reason' => 'Great shift',
        ]);

        $response->assertStatus(302);
    }
}
