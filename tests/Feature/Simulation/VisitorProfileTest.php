<?php

namespace Tests\Feature\Simulation;

use App\Models\LaneBooking;
use App\Models\Visitor;
use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class VisitorProfileTest extends TestCase
{
    use CreatesSimFixtures;

    private function customerWithNoProfile()
    {
        return $this->makeUser(['role' => 'customer']);
    }

    public function test_booking_create_lazily_creates_a_visitor_profile(): void
    {
        $this->clubConfig();
        $user = $this->customerWithNoProfile();
        $this->makeLane();

        $this->actingAs($user)->get(route('visitor.bookings.create'))->assertOk();

        $this->assertNotNull(Visitor::where('user_id', $user->id)->first());
    }

    public function test_fresh_customer_can_book_a_lane_end_to_end(): void
    {
        $this->clubConfig();
        $user = $this->customerWithNoProfile();
        $lane = $this->makeLane();

        $this->actingAs($user)->post(route('visitor.bookings.store'), [
            'lane_id' => $lane->id,
            'time_slot' => 'morning',
            'date' => now()->addDay()->toDateString(),
        ])->assertRedirect(route('visitor.bookings.index'));

        $visitor = Visitor::where('user_id', $user->id)->first();
        $this->assertNotNull($visitor);
        $this->assertSame($user->name, $visitor->name);

        $booking = LaneBooking::where('visitor_id', $visitor->id)->first();
        $this->assertNotNull($booking);
        $this->assertSame('confirmed', $booking->status);
    }

    public function test_customer_can_submit_a_complaint_without_pre_seeded_profile(): void
    {
        $user = $this->customerWithNoProfile();

        $this->actingAs($user)->post(route('visitor.complaints.store'), [
            'type' => 'facility',
            'description' => 'The pinsetter sounds off.',
        ])->assertRedirect(route('visitor.complaints.index'));

        $this->assertNotNull(Visitor::where('user_id', $user->id)->first());
        $this->assertDatabaseHas('complaints', ['description' => 'The pinsetter sounds off.']);
    }

    public function test_queue_and_reviews_pages_load_for_a_fresh_customer(): void
    {
        $user = $this->customerWithNoProfile();
        $this->makeLane();

        $this->actingAs($user)->get(route('visitor.queues.index'))->assertOk();
        $this->actingAs($user)->get(route('visitor.reviews.index'))->assertOk();

        $this->assertNotNull(Visitor::where('user_id', $user->id)->first());
    }

    public function test_customer_dashboard_creates_the_profile(): void
    {
        $user = $this->customerWithNoProfile();

        $this->actingAs($user)->get(route('visitor.dashboard'))->assertOk();

        $this->assertNotNull(Visitor::where('user_id', $user->id)->first());
    }

    public function test_staff_roles_get_no_visitor_profile(): void
    {
        $steward = $this->makeUser(['role' => 'steward']);

        $this->actingAs($steward)->get(route('steward.dashboard'))->assertOk();

        $this->assertNull(Visitor::where('user_id', $steward->id)->first());
    }
}
