<?php

namespace Tests\Feature\Simulation;

use App\Models\Accident;
use App\Models\Inventory;
use App\Models\StaffRelationship;
use App\Services\Simulation\DialogueService;
use Carbon\Carbon;
use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class DialogueServiceTest extends TestCase
{
    use CreatesSimFixtures;

    public function test_bubbles_are_structured_and_bounded(): void
    {
        $this->clubConfig();
        $staff = $this->makeStaff(['happiness' => 50]);

        $bubbles = app(DialogueService::class)->bubblesFor($staff, Carbon::today());

        $this->assertGreaterThan(0, count($bubbles));
        $this->assertLessThanOrEqual(3, count($bubbles));
        foreach ($bubbles as $bubble) {
            $this->assertContains($bubble['type'], ['speech', 'thought', 'exclamation', 'question']);
            $this->assertNotEmpty($bubble['text']);
        }
    }

    public function test_an_accident_on_shift_adds_an_exclamation_bubble(): void
    {
        $this->clubConfig();
        $staff = $this->makeStaff(['happiness' => 50]);
        $shift = $this->makeShift(['staff_id' => $staff->id]);
        Accident::create([
            'staff_id' => $staff->id,
            'shift_id' => $shift->id,
            'type' => 'pinsetter_jam',
            'severity' => 'minor',
            'description' => 'Lane jammed mid-frame',
        ]);

        $bubbles = app(DialogueService::class)->bubblesFor($staff, Carbon::today());

        $this->assertContains('Not my day... the pinsetter jammed on my shift.', array_column($bubbles, 'text'));
    }

    public function test_low_lane_oil_triggers_a_warning_bubble_for_tense_staff(): void
    {
        $this->clubConfig();
        $staff = $this->makeStaff(['happiness' => 50]);
        Inventory::create([
            'name' => 'Lane Oil',
            'category' => 'oil_supplies',
            'quantity' => 2,
            'max_quantity' => 20,
            'reorder_threshold' => 5,
            'cost_per_unit' => 10,
        ]);

        $bubbles = app(DialogueService::class)->bubblesFor($staff, Carbon::today());

        $this->assertContains('We are almost out of lane oil — how do we run a league tonight?', array_column($bubbles, 'text'));
    }

    public function test_happy_staff_gives_an_upbeat_speech_bubble(): void
    {
        $this->clubConfig();
        $staff = $this->makeStaff(['happiness' => 80]);

        $bubbles = app(DialogueService::class)->bubblesFor($staff, Carbon::today());

        $this->assertSame('speech', $bubbles[0]['type']);
    }

    public function test_hostile_relationship_keeps_bubble_structure_valid(): void
    {
        $this->clubConfig();
        $staff = $this->makeStaff(['happiness' => 30]);
        $other = $this->makeStaff(['happiness' => 30]);
        StaffRelationship::create([
            'staff_a_id' => min($staff->id, $other->id),
            'staff_b_id' => max($staff->id, $other->id),
            'level' => 'hostile',
            'score' => -50,
        ]);

        $bubbles = app(DialogueService::class)->bubblesFor($staff, Carbon::today());

        $this->assertSame('exclamation', $bubbles[0]['type']);
        $this->assertNotEmpty($bubbles[0]['text']);
    }
}
