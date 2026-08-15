<?php

namespace Tests\Feature\Simulation;

use App\Models\BookingQueue;
use App\Models\LaneBooking;
use App\Services\Simulation\VisitorSpawner;
use Carbon\Carbon;
use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class VisitorSpawnerTest extends TestCase
{
    use CreatesSimFixtures;

    public function test_promote_queues_moves_waiting_entry_to_a_free_lane(): void
    {
        $this->clubConfig();
        $takenLane = $this->makeLane();
        $freeLane = $this->makeLane();

        $this->makeBooking(['lane_id' => $takenLane->id, 'time_slot' => 'morning', 'status' => 'confirmed']);

        $visitor = $this->makeVisitor();
        $pending = LaneBooking::create([
            'visitor_id' => $visitor->id,
            'lane_id' => $takenLane->id,
            'date' => Carbon::today(),
            'time_slot' => 'morning',
            'status' => 'pending',
            'queue_position' => 1,
        ]);

        BookingQueue::create([
            'booking_id' => $pending->id,
            'visitor_id' => $visitor->id,
            'lane_id' => $takenLane->id,
            'date' => Carbon::today(),
            'time_slot' => 'morning',
            'position' => 1,
            'status' => 'waiting',
        ]);

        $log = $this->simLog();
        app(VisitorSpawner::class)->promoteQueues(Carbon::today(), $log);

        $this->assertSame(1, $log['queues_promoted']);
        $this->assertSame('confirmed', $pending->fresh()->status);
        $this->assertSame($freeLane->id, $pending->fresh()->lane_id);
        $this->assertSame('notified', BookingQueue::first()->fresh()->status);
    }

    public function test_promote_queues_skips_when_no_lane_is_free(): void
    {
        $this->clubConfig();
        $lane = $this->makeLane();
        $this->makeBooking(['lane_id' => $lane->id, 'time_slot' => 'morning', 'status' => 'confirmed']);

        $visitor = $this->makeVisitor();
        $pending = LaneBooking::create([
            'visitor_id' => $visitor->id,
            'lane_id' => $lane->id,
            'date' => Carbon::today(),
            'time_slot' => 'morning',
            'status' => 'pending',
            'queue_position' => 1,
        ]);

        BookingQueue::create([
            'booking_id' => $pending->id,
            'visitor_id' => $visitor->id,
            'lane_id' => $lane->id,
            'date' => Carbon::today(),
            'time_slot' => 'morning',
            'position' => 1,
            'status' => 'waiting',
        ]);

        $log = $this->simLog();
        app(VisitorSpawner::class)->promoteQueues(Carbon::today(), $log);

        $this->assertSame(0, $log['queues_promoted']);
        $this->assertSame('pending', $pending->fresh()->status);
        $this->assertSame('waiting', BookingQueue::first()->fresh()->status);
    }

    public function test_promote_for_slot_only_touches_the_requested_slot(): void
    {
        $this->clubConfig();
        $takenLane = $this->makeLane();
        $this->makeLane();
        $this->makeBooking(['lane_id' => $takenLane->id, 'time_slot' => 'morning', 'status' => 'confirmed']);

        $visitor = $this->makeVisitor();

        $morning = LaneBooking::create([
            'visitor_id' => $visitor->id,
            'lane_id' => $takenLane->id,
            'date' => Carbon::today(),
            'time_slot' => 'morning',
            'status' => 'pending',
            'queue_position' => 1,
        ]);

        $evening = LaneBooking::create([
            'visitor_id' => $visitor->id,
            'lane_id' => $takenLane->id,
            'date' => Carbon::today(),
            'time_slot' => 'evening',
            'status' => 'pending',
            'queue_position' => 1,
        ]);

        foreach (['morning' => $morning, 'evening' => $evening] as $slot => $booking) {
            BookingQueue::create([
                'booking_id' => $booking->id,
                'visitor_id' => $visitor->id,
                'lane_id' => $takenLane->id,
                'date' => Carbon::today(),
                'time_slot' => $slot,
                'position' => 1,
                'status' => 'waiting',
            ]);
        }

        $log = $this->simLog();
        app(VisitorSpawner::class)->promoteForSlot(Carbon::today(), 'morning', $log);

        $this->assertSame(1, $log['queues_promoted']);
        $this->assertSame('confirmed', $morning->fresh()->status);
        $this->assertSame('pending', $evening->fresh()->status);
        $this->assertSame('waiting', BookingQueue::where('time_slot', 'evening')->first()->fresh()->status);
    }

    public function test_run_for_day_never_crashes_and_reports_created_count(): void
    {
        $this->clubConfig();
        $this->makeLane();
        $this->makeLane();
        $this->makeVisitor();
        $this->makeVisitor();

        $log = $this->simLog();
        $created = app(VisitorSpawner::class)->runForDay(Carbon::today()->addDay(), $log);

        $this->assertGreaterThanOrEqual(0, $created);
        $this->assertLessThanOrEqual(2, $created);
    }

    public function test_run_for_day_queues_visitors_when_every_lane_is_booked(): void
    {
        $this->clubConfig();

        $laneIds = [];
        foreach (range(1, 4) as $i) {
            $laneIds[] = $this->makeLane()->id;
        }

        foreach (['morning', 'afternoon', 'evening'] as $slot) {
            foreach ($laneIds as $laneId) {
                $this->makeBooking(['lane_id' => $laneId, 'date' => Carbon::today()->addDay(), 'time_slot' => $slot, 'status' => 'confirmed']);
            }
        }

        foreach (range(1, 8) as $i) {
            $this->makeVisitor();
        }

        $log = $this->simLog();
        $created = app(VisitorSpawner::class)->runForDay(Carbon::today()->addDay(), $log);

        $this->assertGreaterThanOrEqual(0, $created);
        $this->assertSame(0, LaneBooking::whereDate('date', Carbon::today()->addDay())->whereNull('lane_id')->count());

        foreach (LaneBooking::whereDate('date', Carbon::today()->addDay())->where('status', 'pending')->get() as $booking) {
            $this->assertNotNull($booking->lane_id);
            $this->assertTrue(BookingQueue::where('booking_id', $booking->id)->where('status', 'waiting')->exists());
        }
    }
}
