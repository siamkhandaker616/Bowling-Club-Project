<?php

namespace Tests\Feature\Simulation;

use App\Models\BookingQueue;
use App\Models\ClubConfig;
use App\Models\Inventory;
use App\Models\LaneBooking;
use App\Services\Simulation\AccidentEngine;
use App\Services\Simulation\DayCycle;
use Carbon\Carbon;
use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class DayCycleTest extends TestCase
{
    use CreatesSimFixtures;

    public function test_advance_serves_confirmed_bookings_and_increments_the_day(): void
    {
        $this->clubConfig();
        $lane = $this->makeLane();
        $visitor = $this->makeVisitor();
        $this->makeBooking(['visitor_id' => $visitor->id, 'lane_id' => $lane->id, 'status' => 'confirmed']);

        $log = app(DayCycle::class)->advance();

        $this->assertSame(1, $log['bookings_served']);
        $this->assertSame(15, (int) $log['revenue']);
        $this->assertSame('completed', LaneBooking::first()->status);
        $this->assertSame(2, ClubConfig::first()->current_day);
        $this->assertSame(15, (int) ClubConfig::first()->total_revenue);
    }

    public function test_impaired_operations_turn_away_excess_bookings(): void
    {
        $this->clubConfig();
        $lane = $this->makeLane();
        $this->makeLane();
        $visitor = $this->makeVisitor();
        $this->makeBooking(['visitor_id' => $visitor->id, 'lane_id' => $lane->id, 'status' => 'confirmed']);
        Inventory::create([
            'name' => 'Bowling Shoes',
            'category' => 'rental_shoes',
            'quantity' => 0,
            'max_quantity' => 50,
            'reorder_threshold' => 10,
            'cost_per_unit' => 20,
        ]);

        $log = app(DayCycle::class)->advance();

        $this->assertSame(0, $log['bookings_served']);
        $this->assertSame(1, $log['turnaways']);
        $this->assertSame(1, $log['complaints_auto']);

        $booking = LaneBooking::first();
        $this->assertSame('cancelled', $booking->status);
        $this->assertTrue((bool) $booking->compensation_claimed);
        $this->assertSame('free_game', $booking->compensation_type);
    }

    public function test_waiting_queues_are_promoted_before_serving(): void
    {
        $this->clubConfig();
        $takenLane = $this->makeLane();
        $this->makeLane();
        $this->makeBooking(['lane_id' => $takenLane->id, 'status' => 'confirmed']);

        $queuedVisitor = $this->makeVisitor();
        $pending = LaneBooking::create([
            'visitor_id' => $queuedVisitor->id,
            'lane_id' => $takenLane->id,
            'date' => Carbon::today(),
            'time_slot' => 'morning',
            'status' => 'pending',
            'queue_position' => 1,
        ]);

        BookingQueue::create([
            'booking_id' => $pending->id,
            'visitor_id' => $queuedVisitor->id,
            'lane_id' => $takenLane->id,
            'date' => Carbon::today(),
            'time_slot' => 'morning',
            'position' => 1,
            'status' => 'waiting',
        ]);

        $log = app(DayCycle::class)->advance();

        $this->assertSame(1, $log['queues_promoted']);
        $this->assertSame(2, $log['bookings_served']);
        $this->assertSame(30, (int) $log['revenue']);
        $this->assertSame('completed', $pending->fresh()->status);
    }

    public function test_bad_day_advance_generates_role_linked_accidents(): void
    {
        $this->clubConfig(['bad_day_mode' => true]);
        $this->makeLane();
        $caretaker = $this->makeStaff(['role' => 'caretaker']);
        $this->makeShift(['staff_id' => $caretaker->id]);

        $log = app(DayCycle::class)->advance();

        $this->assertCount(1, $log['accidents']);
        $accident = $log['accidents']->first();
        $this->assertArrayHasKey($accident['type'], AccidentEngine::ACCIDENT_TYPES_BY_ROLE['caretaker']);
        $this->assertContains((float) $log['reputation_delta'], [-2.0, -4.0, -7.0]);
        $this->assertLessThan(70, $caretaker->fresh()->happiness);
    }

    public function test_mark_shift_complete_rewards_the_staff_member(): void
    {
        $this->clubConfig();
        $staff = $this->makeStaff(['happiness' => 70, 'performance_score' => 50]);
        $shift = $this->makeShift(['staff_id' => $staff->id]);

        app(DayCycle::class)->markShiftComplete($shift);

        $this->assertSame('completed', $shift->fresh()->status);
        $this->assertSame(75, $staff->fresh()->happiness);
        $this->assertSame(52, $staff->fresh()->performance_score);
        $this->assertDatabaseHas('staff_events', ['staff_id' => $staff->id, 'event_type' => 'worked']);
    }
}
