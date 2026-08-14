<?php

namespace Tests\Feature\Simulation;

use App\Models\Accident;
use App\Services\Simulation\AccidentEngine;
use Carbon\Carbon;
use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class AccidentEngineTest extends TestCase
{
    use CreatesSimFixtures;

    public function test_bad_day_forces_an_accident_for_every_active_staff_on_shift(): void
    {
        $this->clubConfig(['bad_day_mode' => true]);
        $this->makeLane();

        $roles = ['caretaker', 'steward', 'club_manager'];
        foreach ($roles as $role) {
            $staff = $this->makeStaff(['role' => $role]);
            $this->makeShift(['staff_id' => $staff->id]);
        }

        $log = $this->simLog();
        app(AccidentEngine::class)->rollForDay(Carbon::today(), $log);

        $this->assertCount(3, $log['accidents']);

        foreach ($log['accidents'] as $entry) {
            $this->assertGreaterThan(0, $entry['cost']);
            $this->assertContains($entry['severity'], ['minor', 'moderate', 'major']);
        }

        $accidents = Accident::with('staff')->get();
        $this->assertCount(3, $accidents);

        foreach ($accidents as $accident) {
            $this->assertArrayHasKey($accident->type, AccidentEngine::ACCIDENT_TYPES_BY_ROLE[$accident->staff->role]);
            $this->assertLessThan(70, $accident->staff->happiness);
        }
    }

    public function test_normal_day_rolls_a_bounded_number_of_accidents(): void
    {
        $this->clubConfig();
        $this->makeLane();

        foreach (['caretaker', 'caretaker'] as $role) {
            $staff = $this->makeStaff(['role' => $role]);
            $this->makeShift(['staff_id' => $staff->id]);
        }

        $log = $this->simLog();
        app(AccidentEngine::class)->rollForDay(Carbon::today(), $log);

        $this->assertGreaterThanOrEqual(0, $log['accidents']->count());
        $this->assertLessThanOrEqual(2, $log['accidents']->count());
    }
}
