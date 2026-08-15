<?php

namespace Tests\Feature\Simulation;

use App\Models\Accident;
use App\Models\BookingQueue;
use App\Models\Complaint;
use App\Models\LaneBooking;
use App\Models\StaffReview;
use App\Models\TouringRequest;
use App\Services\Simulation\AccidentEngine;
use App\Services\Simulation\DayCycle;
use App\Services\Simulation\VisitorSpawner;
use Carbon\Carbon;
use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class AuditRemediationTest extends TestCase
{
    use CreatesSimFixtures;

    public function test_module_dock_has_no_check_in_action_and_links_facility_map(): void
    {
        $this->clubConfig();
        $caretaker = $this->makeStaff(['role' => 'caretaker']);

        $this->actingAs($caretaker->user)
            ->get(route('caretaker.crew.index'))
            ->assertOk()
            ->assertDontSee('Check In')
            ->assertSee('Facility Map');
    }

    public function test_steward_module_dock_has_no_check_in_action(): void
    {
        $this->clubConfig();
        $steward = $this->makeStaff(['role' => 'steward']);
        $this->makeVisitor();

        $this->actingAs($steward->user)
            ->get(route('steward.visitors.index'))
            ->assertOk()
            ->assertDontSee('Check In');
    }

    public function test_manager_touring_view_shows_real_request_fields(): void
    {
        $this->clubConfig();
        $manager = $this->makeUser(['role' => 'admin']);

        TouringRequest::create([
            'team_name' => 'Striker City',
            'home_club' => 'Midlane Social Club',
            'arrival_date' => Carbon::today()->addDays(2),
            'player_count' => 6,
            'message' => 'Looking forward to visiting.',
            'status' => 'pending',
        ]);

        $this->actingAs($manager)
            ->get(route('manager.touring.index'))
            ->assertOk()
            ->assertSee('Striker City')
            ->assertSee('Midlane Social Club')
            ->assertSee('6')
            ->assertSee('Looking forward to visiting.');
    }

    public function test_accident_log_entries_carry_staff_id(): void
    {
        $this->clubConfig(['bad_day_mode' => true]);
        $staff = $this->makeStaff(['role' => 'caretaker']);
        $this->makeShift(['staff_id' => $staff->id]);

        $log = $this->simLog();
        app(AccidentEngine::class)->rollForDay(Carbon::today(), $log);

        $this->assertTrue($log['accidents']->isNotEmpty());
        $this->assertSame($staff->id, $log['accidents']->first()['staff_id']);
        $this->assertArrayHasKey('staff_name', $log['accidents']->first());
    }

    public function test_free_game_compensation_flags_next_booking_and_skips_revenue(): void
    {
        $this->clubConfig();
        $manager = $this->makeUser(['role' => 'admin']);
        $visitor = $this->makeVisitor();
        $booking = $this->makeBooking(['visitor_id' => $visitor->id, 'date' => Carbon::today(), 'status' => 'confirmed']);

        $complaint = Complaint::create([
            'visitor_id' => $visitor->id,
            'type' => 'service',
            'description' => 'Bad lane condition',
            'status' => 'open',
        ]);

        $this->actingAs($manager)->post(route('manager.complaints.resolve', $complaint), [
            'resolution' => 'Gave a free game',
            'compensation_type' => 'free_game',
        ])->assertRedirect(route('manager.complaints.index'));

        $this->assertTrue((bool) $booking->fresh()->compensation_claimed);
        $this->assertSame('free_game', $booking->fresh()->compensation_type);

        $log = app(DayCycle::class)->advance();

        $this->assertSame(1, $log['bookings_served']);
        $this->assertSame(0, $log['revenue']);
        $this->assertSame('completed', $booking->fresh()->status);
    }

    public function test_priority_queue_compensation_bumps_waiting_entry_to_position_zero(): void
    {
        $this->clubConfig();
        $manager = $this->makeUser(['role' => 'admin']);
        $visitorA = $this->makeVisitor();
        $visitorB = $this->makeVisitor();

        $bookingA = $this->makeBooking(['visitor_id' => $visitorA->id, 'status' => 'pending', 'queue_position' => 5]);
        $entryA = BookingQueue::create([
            'booking_id' => $bookingA->id,
            'visitor_id' => $visitorA->id,
            'lane_id' => $bookingA->lane_id,
            'date' => $bookingA->date,
            'time_slot' => $bookingA->time_slot,
            'position' => 5,
            'status' => 'waiting',
        ]);

        $bookingB = $this->makeBooking(['visitor_id' => $visitorB->id, 'status' => 'pending', 'queue_position' => 4]);
        $entryB = BookingQueue::create([
            'booking_id' => $bookingB->id,
            'visitor_id' => $visitorB->id,
            'lane_id' => $bookingB->lane_id,
            'date' => $bookingB->date,
            'time_slot' => $bookingB->time_slot,
            'position' => 4,
            'status' => 'waiting',
        ]);

        $complaint = Complaint::create([
            'visitor_id' => $visitorA->id,
            'type' => 'service',
            'description' => 'Wanted priority next visit',
            'status' => 'open',
        ]);

        $this->actingAs($manager)->post(route('manager.complaints.resolve', $complaint), [
            'resolution' => 'Bumped to front of queue',
            'compensation_type' => 'priority_queue',
        ])->assertRedirect(route('manager.complaints.index'));

        $this->assertSame(0, $entryA->fresh()->position);
        $this->assertSame(5, $entryB->fresh()->position);
        $this->assertSame(0, $bookingA->fresh()->queue_position);
    }

    public function test_priority_queue_compensation_creates_missing_queue_entry(): void
    {
        $this->clubConfig();
        $manager = $this->makeUser(['role' => 'admin']);
        $visitor = $this->makeVisitor();
        $booking = $this->makeBooking(['visitor_id' => $visitor->id, 'status' => 'pending']);

        $complaint = Complaint::create([
            'visitor_id' => $visitor->id,
            'type' => 'service',
            'description' => 'Compensation request',
            'status' => 'open',
        ]);

        $this->actingAs($manager)->post(route('manager.complaints.resolve', $complaint), [
            'resolution' => 'Front of queue',
            'compensation_type' => 'priority_queue',
        ])->assertRedirect(route('manager.complaints.index'));

        $entry = BookingQueue::where('booking_id', $booking->id)->first();
        $this->assertNotNull($entry);
        $this->assertSame(0, $entry->position);
        $this->assertSame('waiting', $entry->status);
        $this->assertSame(0, $booking->fresh()->queue_position);
    }

    public function test_steward_review_updates_visitor_reputation(): void
    {
        $this->clubConfig();
        $steward = $this->makeStaff(['role' => 'steward']);
        $visitor = $this->makeVisitor();
        $booking = $this->makeBooking([
            'visitor_id' => $visitor->id,
            'date' => Carbon::today(),
            'status' => 'completed',
        ]);

        $this->actingAs($steward->user)->post(route('steward.reviews.store', $visitor), [
            'rating' => 5,
            'body' => 'Great guest',
            'booking_id' => $booking->id,
            'was_polite' => 1,
        ])->assertRedirect(route('steward.visitors.index'));

        $this->assertDatabaseCount('staff_reviews', 1);
        $review = StaffReview::first();
        $this->assertSame($steward->id, $review->staff_id);
        $this->assertSame($visitor->id, $review->visitor_id);
        $this->assertTrue((bool) $review->was_polite);
        $this->assertSame(100, $visitor->fresh()->reputation_score);
    }

    public function test_steward_review_rejects_booking_not_owned_by_visitor(): void
    {
        $this->clubConfig();
        $steward = $this->makeStaff(['role' => 'steward']);
        $visitor = $this->makeVisitor();
        $other = $this->makeBooking(['status' => 'completed']);

        $this->actingAs($steward->user)
            ->post(route('steward.reviews.store', $visitor), [
                'rating' => 3,
                'booking_id' => $other->id,
            ])->assertStatus(404);

        $this->assertDatabaseCount('staff_reviews', 0);
    }

    public function test_promote_queues_prefers_higher_reputation_visitors(): void
    {
        $this->clubConfig();
        $highRep = $this->makeVisitor(['reputation_score' => 90]);
        $lowRep = $this->makeVisitor(['reputation_score' => 30]);
        $lane = $this->makeLane();

        $bookingHigh = $this->makeBooking(['visitor_id' => $highRep->id, 'lane_id' => $lane->id, 'status' => 'pending', 'queue_position' => 2]);
        BookingQueue::create([
            'booking_id' => $bookingHigh->id,
            'visitor_id' => $highRep->id,
            'lane_id' => $lane->id,
            'date' => $bookingHigh->date,
            'time_slot' => 'morning',
            'position' => 2,
            'status' => 'waiting',
        ]);

        $bookingLow = $this->makeBooking(['visitor_id' => $lowRep->id, 'lane_id' => $lane->id, 'status' => 'pending', 'queue_position' => 1]);
        BookingQueue::create([
            'booking_id' => $bookingLow->id,
            'visitor_id' => $lowRep->id,
            'lane_id' => $bookingLow->lane_id,
            'date' => $bookingLow->date,
            'time_slot' => 'morning',
            'position' => 1,
            'status' => 'waiting',
        ]);

        $log = $this->simLog();
        app(VisitorSpawner::class)->promoteQueues(Carbon::today(), $log);

        $this->assertSame('confirmed', $bookingHigh->fresh()->status);
        $this->assertSame('pending', $bookingLow->fresh()->status);
        $this->assertSame(1, $log['queues_promoted']);
    }

    public function test_hire_opens_a_floating_modal_not_a_separate_page(): void
    {
        $this->clubConfig();
        $manager = $this->makeUser(['role' => 'admin']);
        $this->makeStaff();

        $this->actingAs($manager)
            ->get(route('manager.staff.index'))
            ->assertOk()
            ->assertSee('+ Hire Staff')
            ->assertSee('hireModal')
            ->assertSee(route('manager.staff.store'));

        $this->actingAs($manager)->get('/manager/staff/create')->assertNotFound();
    }

    public function test_hire_auto_assigns_2_to_4_non_contradicting_personalities(): void
    {
        $this->clubConfig();
        $manager = $this->makeUser(['role' => 'admin']);

        foreach (['honest', 'stoner', 'overtly_friendly', 'creepy', 'nerd', 'rude', 'cliquey', 'opportunistic'] as $name) {
            $this->personality($name);
        }

        for ($i = 0; $i < 6; $i++) {
            $this->actingAs($manager)->post(route('manager.staff.store'), [
                'name' => 'Hiree ' . $i,
                'email' => 'hiree' . $i . '@test.local',
                'role' => 'caretaker',
                'base_salary' => 2500,
            ])->assertRedirect();

            $staff = \App\Models\Staff::with('personalities')->whereHas('user', fn ($q) => $q->where('email', 'hiree' . $i . '@test.local'))->first();
            $traits = $staff->personalities->pluck('name')->all();

            $this->assertGreaterThanOrEqual(2, count($traits));
            $this->assertLessThanOrEqual(4, count($traits));

            foreach ($traits as $trait) {
                foreach ($traits as $other) {
                    if ($trait === $other) {
                        continue;
                    }
                    $conflicts = match ($trait) {
                        'honest' => ['opportunistic'],
                        'overtly_friendly' => ['rude', 'creepy'],
                        'nerd' => ['stoner'],
                        default => [],
                    };
                    $this->assertNotContains($other, $conflicts, "{$trait} contradicts {$other}");
                }
            }
        }
    }

    public function test_hire_ignores_manual_personality_input(): void
    {
        $this->clubConfig();
        $manager = $this->makeUser(['role' => 'admin']);
        $nerd = $this->personality('nerd');

        $this->actingAs($manager)->post(route('manager.staff.store'), [
            'name' => 'No Manual',
            'email' => 'nomanual@test.local',
            'role' => 'steward',
            'base_salary' => 2500,
            'personalities' => [$nerd->id],
        ])->assertRedirect();

        $staff = \App\Models\Staff::whereHas('user', fn ($q) => $q->where('email', 'nomanual@test.local'))->first();
        $this->assertSame(1, $staff->personalities()->count());
    }

    public function test_accident_relationship_penalty_uses_staff_id_from_log(): void
    {
        $this->clubConfig(['bad_day_mode' => true]);
        $staff = $this->makeStaff(['role' => 'caretaker']);
        $this->makeShift(['staff_id' => $staff->id]);

        $log = $this->simLog();
        app(AccidentEngine::class)->rollForDay(Carbon::today(), $log);

        $staffIds = collect($log['accidents'])->pluck('staff_id')->unique();
        $this->assertTrue($staffIds->contains($staff->id));

        $accidentIds = collect($log['accidents'])->pluck('id')->all();
        $this->assertSame(count($accidentIds), Accident::whereIn('id', $accidentIds)->where('staff_id', $staff->id)->count());
    }
}
