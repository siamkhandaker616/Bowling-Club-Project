<?php

namespace Tests\Feature\Simulation;

use App\Models\Complaint;
use App\Models\Fixture;
use App\Models\FixturePrep;
use App\Models\Inventory;
use App\Models\Lane;
use App\Models\League;
use App\Models\Team;
use App\Models\TeamMember;
use App\Services\Simulation\DayCycle;
use App\Services\Simulation\MatchService;
use Carbon\Carbon;
use Tests\Concerns\CreatesSimFixtures;
use Tests\TestCase;

class LeagueSimTest extends TestCase
{
    use CreatesSimFixtures;

    private function makeLeague(string $name = 'Test League'): League
    {
        return League::create(['name' => $name, 'season' => 'Summer 2026']);
    }

    private function makeTeam(League $league, string $name, int $strength = 180): Team
    {
        $team = Team::create(['name' => $name, 'league_id' => $league->id, 'wins' => 0, 'losses' => 0, 'draws' => 0]);
        TeamMember::create(['team_id' => $team->id, 'name' => $name . ' Member', 'average_score' => $strength]);

        return $team;
    }

    private function makeFixture(Team $home, Team $away, League $league, Carbon $date, string $status = 'upcoming'): Fixture
    {
        return Fixture::create([
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'date' => $date->toDateString(),
            'time' => '18:00',
            'lane_id' => $this->makeLane()->id,
            'league_id' => $league->id,
            'status' => $status,
        ]);
    }

    public function test_manager_league_hub_lists_upcoming_fixtures_with_readiness(): void
    {
        $this->clubConfig();
        $manager = $this->makeUser(['role' => 'admin']);
        $league = $this->makeLeague();
        $home = $this->makeTeam($league, 'Thunder Rollers');
        $away = $this->makeTeam($league, 'Pin Crushers');
        $this->makeFixture($home, $away, $league, Carbon::today()->addDays(2));

        $this->actingAs($manager)
            ->get(route('manager.league.index'))
            ->assertOk()
            ->assertSee('Thunder Rollers')
            ->assertSee('Pin Crushers')
            ->assertSee('Welcome Team')
            ->assertSee('Kits')
            ->assertSee('Lane')
            ->assertSee('Training');
    }

    public function test_manager_welcomes_away_team_on_upcoming_fixture(): void
    {
        $this->clubConfig();
        $manager = $this->makeUser(['role' => 'admin']);
        $league = $this->makeLeague();
        $home = $this->makeTeam($league, 'Thunder Rollers');
        $away = $this->makeTeam($league, 'Pin Crushers');
        $fixture = $this->makeFixture($home, $away, $league, Carbon::today()->addDays(2));

        $this->actingAs($manager)
            ->post(route('manager.league.welcome', $fixture))
            ->assertRedirect(route('manager.league.index'));

        $prep = FixturePrep::where('fixture_id', $fixture->id)->first();
        $this->assertNotNull($prep);
        $this->assertNotNull($prep->welcomed_at);
    }

    public function test_manager_cannot_welcome_a_completed_fixture(): void
    {
        $this->clubConfig();
        $manager = $this->makeUser(['role' => 'admin']);
        $league = $this->makeLeague();
        $home = $this->makeTeam($league, 'Thunder Rollers');
        $away = $this->makeTeam($league, 'Pin Crushers');
        $fixture = $this->makeFixture($home, $away, $league, Carbon::today(), 'completed');

        $this->actingAs($manager)
            ->post(route('manager.league.welcome', $fixture))
            ->assertRedirect(route('manager.league.index'));

        $this->assertDatabaseMissing('fixture_preps', ['fixture_id' => $fixture->id]);
    }

    public function test_caretaker_prep_queue_shows_only_in_window_fixtures(): void
    {
        $this->clubConfig();
        $caretaker = $this->makeStaff(['role' => 'caretaker']);
        $league = $this->makeLeague();
        $home = $this->makeTeam($league, 'Thunder Rollers');
        $away = $this->makeTeam($league, 'Pin Crushers');
        $this->makeFixture($home, $away, $league, Carbon::today()->addDays(2));
        $this->makeFixture($home, $away, $league, Carbon::today()->addDays(10));

        $this->actingAs($caretaker->user)
            ->get(route('caretaker.prep.index'))
            ->assertOk()
            ->assertSee('Thunder Rollers')
            ->assertSee('Prep Kits');
    }

    public function test_caretaker_prepares_kits_lane_and_training(): void
    {
        $this->clubConfig();
        $caretaker = $this->makeStaff(['role' => 'caretaker']);
        $this->makeInventory(['name' => 'Ball Polish', 'quantity' => 14, 'max_quantity' => 24, 'reorder_threshold' => 6]);
        $this->makeInventory(['name' => 'Score Sheets', 'quantity' => 100, 'max_quantity' => 200, 'reorder_threshold' => 40]);

        $league = $this->makeLeague();
        $home = $this->makeTeam($league, 'Thunder Rollers');
        $away = $this->makeTeam($league, 'Pin Crushers');
        $fixture = $this->makeFixture($home, $away, $league, Carbon::today()->addDays(2));
        $lane = Lane::find($fixture->lane_id);

        $this->actingAs($caretaker->user)
            ->post(route('caretaker.prep.prepare', [$fixture, 'kits']))
            ->assertRedirect(route('caretaker.prep.index'));

        $this->assertSame(13, Inventory::where('name', 'Ball Polish')->first()->quantity);
        $this->assertSame(95, Inventory::where('name', 'Score Sheets')->first()->quantity);
        $this->assertTrue((bool) FixturePrep::where('fixture_id', $fixture->id)->first()->kits_ready);

        $this->actingAs($caretaker->user)
            ->post(route('caretaker.prep.prepare', [$fixture, 'lane']))
            ->assertRedirect(route('caretaker.prep.index'));

        $lane->refresh();
        $this->assertSame('reserved', $lane->status);
        $this->assertSame(100, $lane->oil_level);
        $this->assertTrue((bool) FixturePrep::where('fixture_id', $fixture->id)->first()->lane_ready);

        $this->actingAs($caretaker->user)
            ->post(route('caretaker.prep.prepare', [$fixture, 'training']))
            ->assertRedirect(route('caretaker.prep.index'));

        $this->assertTrue((bool) FixturePrep::where('fixture_id', $fixture->id)->first()->training_ready);
    }

    public function test_caretaker_kits_prep_blocked_when_critical_stock_empty(): void
    {
        $this->clubConfig();
        $caretaker = $this->makeStaff(['role' => 'caretaker']);
        $this->makeInventory(['name' => 'Bowling Shoes', 'quantity' => 0, 'max_quantity' => 50, 'reorder_threshold' => 10]);

        $league = $this->makeLeague();
        $home = $this->makeTeam($league, 'Thunder Rollers');
        $away = $this->makeTeam($league, 'Pin Crushers');
        $fixture = $this->makeFixture($home, $away, $league, Carbon::today()->addDays(2));

        $this->actingAs($caretaker->user)
            ->post(route('caretaker.prep.prepare', [$fixture, 'kits']))
            ->assertRedirect(route('caretaker.prep.index'));

        $this->assertDatabaseMissing('fixture_preps', ['fixture_id' => $fixture->id]);
    }

    public function test_inactive_caretaker_cannot_prep(): void
    {
        $this->clubConfig();
        $caretaker = $this->makeStaff(['role' => 'caretaker', 'is_active' => false]);

        $league = $this->makeLeague();
        $home = $this->makeTeam($league, 'Thunder Rollers');
        $away = $this->makeTeam($league, 'Pin Crushers');
        $fixture = $this->makeFixture($home, $away, $league, Carbon::today()->addDays(2));

        $this->actingAs($caretaker->user)
            ->post(route('caretaker.prep.prepare', [$fixture, 'lane']))
            ->assertForbidden();
    }

    public function test_advance_goes_live_then_completes_a_prepared_match(): void
    {
        $this->clubConfig();
        $league = $this->makeLeague();
        $home = $this->makeTeam($league, 'Thunder Rollers', 250);
        $away = $this->makeTeam($league, 'Pin Crushers', 140);
        $fixture = $this->makeFixture($home, $away, $league, Carbon::today());
        $lane = Lane::find($fixture->lane_id);

        $service = app(MatchService::class);
        $service->welcome($fixture, null);
        $service->prepKits($fixture, null);
        $service->prepLane($fixture, null);
        $service->prepTraining($fixture, null);

        $first = app(DayCycle::class)->advance();

        $this->assertSame('live', $fixture->fresh()->status);
        $this->assertSame(1, $first['matches']->count());
        $this->assertSame('live', $first['matches']->first()['status']);

        $second = app(DayCycle::class)->advance();

        $this->assertSame('completed', $fixture->fresh()->status);
        $this->assertSame('open', $lane->fresh()->status);
        $this->assertSame(1, $home->fresh()->wins);
        $this->assertSame(1, $away->fresh()->losses);
        $this->assertSame(0, $second['league_penalties']);
        $this->assertSame(180.0, (float) $second['match_revenue']);
    }

    public function test_unprepared_match_completes_with_penalty(): void
    {
        $this->clubConfig();
        $league = $this->makeLeague();
        $home = $this->makeTeam($league, 'Thunder Rollers');
        $away = $this->makeTeam($league, 'Pin Crushers');
        $fixture = $this->makeFixture($home, $away, $league, Carbon::today());

        app(DayCycle::class)->advance();
        $second = app(DayCycle::class)->advance();

        $this->assertSame('completed', $fixture->fresh()->status);
        $this->assertSame(1, $second['league_penalties']);
        $this->assertSame(60.0, (float) $second['match_revenue']);
        $this->assertSame(1, Complaint::where('type', 'league')->count());
    }
}
