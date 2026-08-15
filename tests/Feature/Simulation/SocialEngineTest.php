<?php

namespace Tests\Feature\Simulation;

use App\Models\StaffRelationship;
use App\Services\Simulation\SocialEngine;
use Carbon\Carbon;
use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class SocialEngineTest extends TestCase
{
    use CreatesSimFixtures;

    public function test_daily_drift_builds_relationship_between_shift_mates(): void
    {
        $this->clubConfig();
        $this->makeLane();
        $a = $this->makeStaff(['happiness' => 80]);
        $b = $this->makeStaff(['happiness' => 80]);
        $this->makeShift(['staff_id' => $a->id]);
        $this->makeShift(['staff_id' => $b->id]);

        $log = $this->simLog();
        app(SocialEngine::class)->dailyDrift(Carbon::today(), $log);

        $rel = StaffRelationship::first();
        $this->assertNotNull($rel);
        $this->assertSame(3, $rel->score);
        $this->assertSame('neutral', $rel->level);
    }

    public function test_daily_drift_flips_relationship_level_and_logs_it(): void
    {
        $this->clubConfig();
        $this->makeLane();
        $a = $this->makeStaff(['happiness' => 80]);
        $b = $this->makeStaff(['happiness' => 80]);
        $this->makeShift(['staff_id' => $a->id]);
        $this->makeShift(['staff_id' => $b->id]);

        StaffRelationship::create([
            'staff_a_id' => min($a->id, $b->id),
            'staff_b_id' => max($a->id, $b->id),
            'level' => 'friendly',
            'score' => 24,
        ]);

        $log = $this->simLog();
        app(SocialEngine::class)->dailyDrift(Carbon::today(), $log);

        $rel = StaffRelationship::first();
        $this->assertSame(27, $rel->score);
        $this->assertSame('trusted', $rel->level);
        $this->assertCount(1, $log['relationship_changes']);
        $this->assertDatabaseHas('staff_events', ['event_type' => 'social']);
    }

    public function test_vent_creates_trash_talk_and_releases_steam(): void
    {
        $this->clubConfig();
        $staff = $this->makeStaff(['happiness' => 50]);

        $log = $this->simLog();
        app(SocialEngine::class)->vent($staff, Carbon::today(), $log);

        $this->assertSame(52, $staff->fresh()->happiness);
        $this->assertCount(1, $log['trash_talk']);
        $this->assertCount(0, $log['snitches']);
        $this->assertDatabaseHas('staff_events', ['staff_id' => $staff->id, 'event_type' => 'trash_talk']);
    }
}
