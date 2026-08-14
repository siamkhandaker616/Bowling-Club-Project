<?php

namespace Tests\Feature\Simulation;

use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class ReapplyTest extends TestCase
{
    use CreatesSimFixtures;

    public function test_fired_staff_can_reapply_with_a_fresh_role(): void
    {
        $this->clubConfig();
        $staff = $this->makeStaff(['is_active' => false], ['is_active' => false]);
        $user = $staff->user;

        $this->actingAs($user)->get(route('reapply.index'))->assertOk();

        $response = $this->actingAs($user)->post(route('reapply.store'), [
            'name' => 'Fresh Start',
            'role' => 'steward',
        ]);
        $response->assertRedirect(route('steward.dashboard'));

        $user->refresh();
        $staff->refresh();

        $this->assertSame('steward', $user->role);
        $this->assertTrue((bool) $user->is_active);
        $this->assertSame('Fresh Start', $user->name);
        $this->assertTrue((bool) $staff->is_active);
        $this->assertSame('steward', $staff->role);
        $this->assertSame(70, $staff->happiness);
        $this->assertDatabaseHas('staff_events', ['staff_id' => $staff->id, 'event_type' => 'hired']);
    }

    public function test_active_staff_cannot_reapply(): void
    {
        $this->clubConfig();
        $staff = $this->makeStaff();
        $user = $staff->user;

        $this->actingAs($user)->get(route('reapply.index'))->assertRedirect(route('dashboard'));
        $this->actingAs($user)->post(route('reapply.store'), [
            'name' => 'Sneaky',
            'role' => 'caretaker',
        ])->assertForbidden();
    }
}
