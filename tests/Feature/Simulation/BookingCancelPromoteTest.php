<?php

namespace Tests\Feature\Simulation;

use App\Models\BookingQueue;
use App\Models\LaneBooking;
use Carbon\Carbon;
use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class BookingCancelPromoteTest extends TestCase
{
    use CreatesSimFixtures;

    public function test_cancelling_a_booking_promotes_the_next_queued_visitor(): void
    {
        $this->clubConfig();
        $admin = $this->makeUser(['role' => 'admin']);
        $lane = $this->makeLane();
        $this->makeLane();

        $confirmed = $this->makeBooking(['lane_id' => $lane->id, 'status' => 'confirmed']);

        $queuedVisitor = $this->makeVisitor();
        $pending = LaneBooking::create([
            'visitor_id' => $queuedVisitor->id,
            'lane_id' => $lane->id,
            'date' => Carbon::today(),
            'time_slot' => 'morning',
            'status' => 'pending',
            'queue_position' => 1,
        ]);

        BookingQueue::create([
            'booking_id' => $pending->id,
            'visitor_id' => $queuedVisitor->id,
            'lane_id' => $lane->id,
            'date' => Carbon::today(),
            'time_slot' => 'morning',
            'position' => 1,
            'status' => 'waiting',
        ]);

        $response = $this->actingAs($admin)->post(route('manager.bookings.cancel', $confirmed));

        $response->assertRedirect(route('manager.bookings.index'));

        $this->assertSame('cancelled', $confirmed->fresh()->status);
        $this->assertSame('confirmed', $pending->fresh()->status);
        $this->assertSame('notified', BookingQueue::first()->fresh()->status);
        $this->assertNull($pending->fresh()->queue_position);
    }
}
