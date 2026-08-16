<?php

namespace Tests\Feature\Simulation;

use App\Models\BookingQueue;
use App\Models\ClubConfig;
use App\Models\Complaint;
use App\Models\Confrontation;
use App\Models\Event;
use App\Models\Fixture;
use App\Models\League;
use App\Models\Penalty;
use App\Models\ReviewVote;
use App\Models\Shift;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\VisitorReview;
use App\Services\Simulation\ConfrontationService;
use App\Services\Simulation\DayCycle;
use App\Services\Simulation\MatchService;
use App\Services\Simulation\VisitorSpawner;
use Carbon\Carbon;
use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class AuditRemediationTwoTest extends TestCase
{
    use CreatesSimFixtures;

    private function makeLeague(string $name = 'Test League'): League
    {
        return League::create(['name' => $name, 'season' => 'Summer 2026']);
    }

    private function makeTeam(League $league, string $name): Team
    {
        $team = Team::create(['name' => $name, 'league_id' => $league->id, 'wins' => 0, 'losses' => 0, 'draws' => 0]);
        TeamMember::create(['team_id' => $team->id, 'name' => $name . ' Member', 'average_score' => 180]);

        return $team;
    }

    private function makeFixture(Team $home, Team $away, League $league, Carbon $date, string $status = 'upcoming'): Fixture
    {
        return Fixture::create([
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'league_id' => $league->id,
            'date' => $date->toDateString(),
            'time' => '18:00',
            'lane_id' => $this->makeLane()->id,
            'status' => $status,
        ]);
    }

    private function resolveComplaint(Complaint $complaint, string $compensation): void
    {
        $manager = $this->makeUser(['role' => 'admin']);

        $this->actingAs($manager)
            ->post(route('manager.complaints.resolve', $complaint), [
                'resolution' => 'Resolved with ' . $compensation . '.',
                'compensation_type' => $compensation,
            ])
            ->assertRedirect(route('manager.complaints.index'));
    }

    public function test_catch_up_advances_sim_for_elapsed_days(): void
    {
        $this->clubConfig(['last_advanced_at' => now()->subDays(3)]);
        $manager = $this->makeUser(['role' => 'admin']);

        $this->actingAs($manager)->getJson(route('manager.day.stats'))->assertOk();

        $this->assertSame(4, ClubConfig::singleton()->current_day);
        $this->assertNotNull(ClubConfig::singleton()->last_advanced_at);
    }

    public function test_catch_up_stamps_fresh_anchor_without_advancing(): void
    {
        $this->clubConfig();
        $manager = $this->makeUser(['role' => 'admin']);

        $this->actingAs($manager)->getJson(route('manager.day.stats'))->assertOk();

        $this->assertSame(1, ClubConfig::singleton()->current_day);
        $this->assertNotNull(ClubConfig::singleton()->last_advanced_at);
    }

    public function test_catch_up_is_capped_at_fourteen_days(): void
    {
        $this->clubConfig(['last_advanced_at' => now()->subDays(30)]);
        $manager = $this->makeUser(['role' => 'admin']);

        $this->actingAs($manager)->getJson(route('manager.day.stats'))->assertOk();

        $this->assertSame(15, ClubConfig::singleton()->current_day);
    }

    public function test_catch_up_skips_non_sim_routes(): void
    {
        $this->clubConfig(['last_advanced_at' => now()->subDays(5)]);
        $user = $this->makeUser();

        $this->actingAs($user)->get(route('profile.edit'))->assertOk();

        $this->assertSame(1, ClubConfig::singleton()->current_day);
    }

    public function test_refund_compensation_flags_next_booking_and_refunds_revenue(): void
    {
        $this->clubConfig();
        $visitor = $this->makeVisitor();
        $booking = $this->makeBooking(['visitor_id' => $visitor->id]);
        $complaint = Complaint::create([
            'visitor_id' => $visitor->id,
            'type' => 'accident',
            'description' => 'Spilled drink on the approach.',
            'status' => 'open',
        ]);

        $this->resolveComplaint($complaint, 'refund');

        $booking->refresh();
        $this->assertTrue($booking->compensation_claimed);
        $this->assertSame('refund', $booking->compensation_type);

        $log = app(DayCycle::class)->advance();

        $booking->refresh();
        $this->assertSame('completed', $booking->status);
        $this->assertEquals(15.0, $log['refunds']);
        $this->assertEquals(0, $log['revenue']);
        $this->assertEquals(15.0, $log['expenses']);
    }

    public function test_discount_compensation_charges_half_revenue(): void
    {
        $this->clubConfig();
        $visitor = $this->makeVisitor();
        $booking = $this->makeBooking(['visitor_id' => $visitor->id]);
        $complaint = Complaint::create([
            'visitor_id' => $visitor->id,
            'type' => 'staff',
            'description' => 'Rude service.',
            'status' => 'open',
        ]);

        $this->resolveComplaint($complaint, 'discount');

        $log = app(DayCycle::class)->advance();

        $this->assertEquals(7.5, $log['revenue']);
        $this->assertEquals(0, $log['refunds']);
    }

    public function test_apology_compensation_bumps_reputation(): void
    {
        $this->clubConfig();
        $visitor = $this->makeVisitor();
        $complaint = Complaint::create([
            'visitor_id' => $visitor->id,
            'type' => 'staff',
            'description' => 'Long wait.',
            'status' => 'open',
        ]);

        $this->resolveComplaint($complaint, 'apology');

        $this->assertSame(76, ClubConfig::singleton()->reputation);
    }

    public function test_apology_compensation_clamps_reputation_at_one_hundred(): void
    {
        $this->clubConfig(['reputation' => 100]);
        $visitor = $this->makeVisitor();
        $complaint = Complaint::create([
            'visitor_id' => $visitor->id,
            'type' => 'staff',
            'description' => 'Minor gripe.',
            'status' => 'open',
        ]);

        $this->resolveComplaint($complaint, 'apology');

        $this->assertSame(100, ClubConfig::singleton()->reputation);
    }

    public function test_priority_queue_compensation_promotes_before_higher_reputation(): void
    {
        $this->clubConfig();
        $lane = $this->makeLane();
        $low = $this->makeVisitor(['reputation_score' => 40]);
        $high = $this->makeVisitor(['reputation_score' => 90]);

        $bookingLow = $this->makeBooking([
            'visitor_id' => $low->id,
            'lane_id' => $lane->id,
            'status' => 'pending',
            'compensation_type' => 'priority_queue',
        ]);
        $bookingHigh = $this->makeBooking([
            'visitor_id' => $high->id,
            'lane_id' => $lane->id,
            'status' => 'pending',
        ]);

        BookingQueue::create([
            'booking_id' => $bookingLow->id,
            'visitor_id' => $low->id,
            'lane_id' => $lane->id,
            'date' => Carbon::today(),
            'time_slot' => 'morning',
            'position' => 2,
            'status' => 'waiting',
        ]);
        BookingQueue::create([
            'booking_id' => $bookingHigh->id,
            'visitor_id' => $high->id,
            'lane_id' => $lane->id,
            'date' => Carbon::today(),
            'time_slot' => 'morning',
            'position' => 1,
            'status' => 'waiting',
        ]);

        $log = $this->simLog();
        app(VisitorSpawner::class)->promoteQueues(Carbon::today(), $log);

        $bookingLow->refresh();
        $bookingHigh->refresh();

        $this->assertSame('confirmed', $bookingLow->status);
        $this->assertNull($bookingLow->compensation_type);
        $this->assertSame('pending', $bookingHigh->status);
        $this->assertSame(1, $log['queues_promoted']);
    }

    public function test_advance_generates_next_round_within_same_league(): void
    {
        $this->clubConfig();
        $league = $this->makeLeague();
        $a = $this->makeTeam($league, 'Thunder Rollers');
        $b = $this->makeTeam($league, 'Pin Crushers');

        $created = app(MatchService::class)->generateNextRound(Carbon::today());

        $this->assertSame(1, $created);

        $fixture = Fixture::where('league_id', $league->id)->where('status', 'upcoming')->first();
        $this->assertNotNull($fixture);
        $this->assertContains($fixture->home_team_id, [$a->id, $b->id]);
        $this->assertContains($fixture->away_team_id, [$a->id, $b->id]);
    }

    public function test_generate_next_round_avoids_repeating_pairings(): void
    {
        $this->clubConfig();
        $league = $this->makeLeague();
        $a = $this->makeTeam($league, 'Alpha');
        $b = $this->makeTeam($league, 'Bravo');
        $c = $this->makeTeam($league, 'Charlie');

        $today = Carbon::today()->subDays(3);
        $this->makeFixture($a, $b, $league, $today, 'completed');
        $this->makeFixture($b, $c, $league, $today->copy()->addDay(), 'completed');
        $this->makeFixture($c, $a, $league, $today->copy()->addDays(2), 'completed');

        $created = app(MatchService::class)->generateNextRound(Carbon::today());

        $this->assertSame(0, $created);
    }

    public function test_penalized_verdict_docks_salary_and_increments_warnings(): void
    {
        $this->clubConfig();
        $reporter = $this->makeStaff(['role' => 'caretaker']);
        $accused = $this->makeStaff(['role' => 'caretaker']);

        $confrontation = Confrontation::create([
            'reporter_staff_id' => $reporter->id,
            'accused_staff_id' => $accused->id,
            'incident_type' => 'theft',
            'incident_description' => 'Suspected stock misuse.',
            'db_verified' => true,
            'date' => Carbon::today(),
        ]);

        app(ConfrontationService::class)->verdict($confrontation, 'penalized', 100);

        $accused->refresh();
        $this->assertEquals(2400.0, $accused->current_salary);
        $this->assertSame(1, $accused->warnings_count);
        $this->assertSame(1, Penalty::count());
    }

    public function test_cash_bonus_records_a_real_expense(): void
    {
        $this->clubConfig();
        $manager = $this->makeUser(['role' => 'admin']);
        $staff = $this->makeStaff(['role' => 'caretaker']);

        $this->actingAs($manager)
            ->post(route('manager.staff.bonus', $staff), [
                'type' => 'cash',
                'amount_or_hours' => 500,
                'reason' => 'Quarterly performance.',
            ])
            ->assertRedirect(route('manager.staff.show', $staff));

        $this->assertEquals(500.0, ClubConfig::singleton()->total_expenses);
    }

    public function test_steward_schedule_hides_mark_complete_for_completed_shifts(): void
    {
        $this->clubConfig();
        $steward = $this->makeStaff(['role' => 'steward']);
        $this->makeShift(['staff_id' => $steward->id, 'status' => 'completed']);

        $this->actingAs($steward->user)
            ->get(route('steward.schedule.index'))
            ->assertOk()
            ->assertDontSee('Mark Complete');
    }

    public function test_visitor_reviews_render_voted_state(): void
    {
        $this->clubConfig();
        $customer = $this->makeUser(['role' => 'customer']);
        $visitor = $this->makeVisitor();
        $booking = $this->makeBooking(['visitor_id' => $visitor->id]);

        $review = VisitorReview::create([
            'visitor_id' => $visitor->id,
            'booking_id' => $booking->id,
            'rating' => 5,
            'body' => 'Great night out.',
            'helpful_count' => 1,
            'not_helpful_count' => 0,
        ]);

        ReviewVote::create([
            'review_id' => $review->id,
            'review_type' => 'visitor_review',
            'voter_id' => $customer->id,
            'vote' => 'helpful',
        ]);

        $this->actingAs($customer)
            ->get(route('visitor.reviews.index'))
            ->assertOk()
            ->assertSee('Great night out.')
            ->assertSee('background:var(--sky-dark);color:var(--pin-white);');
    }

    public function test_steward_visitors_shows_premium_badge_for_premium_tier(): void
    {
        $this->clubConfig();
        $steward = $this->makeStaff(['role' => 'steward']);
        $this->makeVisitor(['tier' => 'premium']);

        $this->actingAs($steward->user)
            ->get(route('steward.visitors.index'))
            ->assertOk()
            ->assertSee('Premium');
    }

    public function test_caretaker_shifts_render_with_valid_statuses(): void
    {
        $this->clubConfig();
        $caretaker = $this->makeStaff(['role' => 'caretaker']);
        $this->makeShift(['staff_id' => $caretaker->id, 'status' => 'in_progress']);
        $this->makeShift(['staff_id' => $caretaker->id, 'status' => 'scheduled']);

        $this->actingAs($caretaker->user)
            ->get(route('caretaker.shifts.index'))
            ->assertOk()
            ->assertSee('>Complete</button>', false);
    }

    public function test_manager_reviews_show_helpful_counts(): void
    {
        $this->clubConfig();
        $manager = $this->makeUser(['role' => 'admin']);
        $visitor = $this->makeVisitor();
        $booking = $this->makeBooking(['visitor_id' => $visitor->id]);

        VisitorReview::create([
            'visitor_id' => $visitor->id,
            'booking_id' => $booking->id,
            'rating' => 4,
            'body' => 'Solid lane conditions.',
            'helpful_count' => 2,
            'not_helpful_count' => 1,
        ]);

        $this->actingAs($manager)
            ->get(route('manager.reviews.index'))
            ->assertOk()
            ->assertSee('2 helpful')
            ->assertSee('1 not helpful');
    }

    public function test_manager_inventory_stock_percent_capped_at_one_hundred(): void
    {
        $this->clubConfig();
        $manager = $this->makeUser(['role' => 'admin']);
        $this->makeInventory(['quantity' => 40, 'max_quantity' => 20, 'reorder_threshold' => 5]);

        $this->actingAs($manager)
            ->get(route('manager.inventory.index'))
            ->assertOk()
            ->assertSee('100%')
            ->assertDontSee('200%');
    }

    public function test_manager_league_hub_renders_empty_standings_grid(): void
    {
        $this->clubConfig();
        $manager = $this->makeUser(['role' => 'admin']);

        $this->actingAs($manager)
            ->get(route('manager.league.index'))
            ->assertOk();
    }

    public function test_customer_dashboard_shows_upcoming_event_title_and_venue(): void
    {
        $this->clubConfig();
        $customer = $this->makeUser(['role' => 'customer']);

        Event::create([
            'title' => 'Pins and Pints Night',
            'description' => 'Social bowl night.',
            'date' => Carbon::today()->addDays(5)->toDateString(),
            'time' => '19:00',
            'venue' => 'Main Lane Floor',
            'max_capacity' => 40,
            'current_rsvps' => 5,
            'price' => 0,
        ]);

        Event::create([
            'title' => 'Old Tournament',
            'description' => 'Already over.',
            'date' => Carbon::today()->subDays(2)->toDateString(),
            'time' => '10:00',
            'venue' => 'Lanes 1-4',
            'max_capacity' => 24,
            'current_rsvps' => 24,
            'price' => 500,
        ]);

        $this->actingAs($customer)
            ->get(route('visitor.dashboard'))
            ->assertOk()
            ->assertSee('Pins and Pints Night')
            ->assertSee('Main Lane Floor')
            ->assertDontSee('Old Tournament');
    }
}
