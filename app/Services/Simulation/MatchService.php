<?php

namespace App\Services\Simulation;

use App\Models\Complaint;
use App\Models\Fixture;
use App\Models\FixturePrep;
use App\Models\Inventory;
use App\Models\Lane;
use App\Models\League;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MatchService
{
    public const PREP_WINDOW_DAYS = 3;

    private const KIT_USAGE = [
        'Ball Polish' => -1,
        'Score Sheets' => -5,
    ];

    private const CRITICAL_KITS = ['Bowling Shoes', 'Spare Pins'];

    public function __construct(private InventoryService $inventory)
    {
    }

    public function prep(Fixture $fixture): FixturePrep
    {
        return FixturePrep::firstOrCreate(['fixture_id' => $fixture->id]);
    }

    public function readiness(Fixture $fixture): array
    {
        $prep = $this->prep($fixture);

        return [
            'welcomed' => (bool) $prep->welcomed_at,
            'kits' => (bool) $prep->kits_ready,
            'lane' => $fixture->lane_id === null || (bool) $prep->lane_ready,
            'training' => (bool) $prep->training_ready,
        ];
    }

    public function isPrepared(Fixture $fixture): bool
    {
        $ready = $this->readiness($fixture);

        return $ready['welcomed'] && $ready['kits'] && $ready['lane'] && $ready['training'];
    }

    public function welcome(Fixture $fixture, ?int $staffId = null): array
    {
        if ($fixture->status !== 'upcoming') {
            return ['ok' => false, 'message' => 'This fixture is no longer upcoming.'];
        }

        $prep = $this->prep($fixture);
        $prep->update(['welcomed_by' => $staffId, 'welcomed_at' => now()]);

        return ['ok' => true, 'message' => $fixture->awayTeam->name . ' welcomed for their visit.'];
    }

    public function prepKits(Fixture $fixture, ?int $staffId = null): array
    {
        foreach (self::CRITICAL_KITS as $name) {
            $item = Inventory::where('name', $name)->first();
            if ($item && $item->quantity <= 0) {
                return ['ok' => false, 'message' => $name . ' is at 0 — restock before prepping kits.'];
            }
        }

        foreach (self::KIT_USAGE as $name => $change) {
            $item = Inventory::where('name', $name)->first();
            if ($item) {
                $this->inventory->adjust($item, $change, 'usage', 'Match kit prep for fixture #' . $fixture->id, $staffId);
            }
        }

        $prep = $this->prep($fixture);
        $prep->update(['kits_ready' => true, 'kits_prepared_by' => $staffId, 'kits_prepared_at' => now()]);

        return ['ok' => true, 'message' => 'Match kits bagged and ready.'];
    }

    public function prepLane(Fixture $fixture, ?int $staffId = null): array
    {
        if (! $fixture->lane_id) {
            return ['ok' => false, 'message' => 'This fixture has no lane assigned.'];
        }

        $lane = Lane::find($fixture->lane_id);
        if (! $lane) {
            return ['ok' => false, 'message' => 'Assigned lane is missing.'];
        }

        $lane->status = 'reserved';
        $lane->oil_level = 100;
        $lane->last_maintained_at = now();
        $lane->save();

        $prep = $this->prep($fixture);
        $prep->update(['lane_ready' => true, 'lane_prepared_by' => $staffId, 'lane_prepared_at' => now()]);

        return ['ok' => true, 'message' => 'Lane ' . $lane->lane_number . ' oiled, dressed and reserved for match night.'];
    }

    public function prepTraining(Fixture $fixture, ?int $staffId = null): array
    {
        $prep = $this->prep($fixture);
        $prep->update(['training_ready' => true, 'training_prepared_by' => $staffId, 'training_prepared_at' => now()]);

        return ['ok' => true, 'message' => 'Training facilities prepped for match night.'];
    }

    public function dueMatches(Carbon $date): Collection
    {
        return Fixture::with(['homeTeam', 'awayTeam', 'league', 'lane'])
            ->where('status', 'upcoming')
            ->whereDate('date', $date)
            ->get();
    }

    public function upcomingFixtures(Carbon $date): Collection
    {
        return Fixture::with(['homeTeam', 'awayTeam', 'league', 'lane'])
            ->where('status', 'upcoming')
            ->whereDate('date', '>=', $date)
            ->orderBy('date')
            ->orderBy('time')
            ->get();
    }

    public function prepWindowFixtures(Carbon $date): Collection
    {
        $window = $date->copy()->addDays(self::PREP_WINDOW_DAYS);

        return Fixture::with(['homeTeam', 'awayTeam', 'league', 'lane'])
            ->where('status', 'upcoming')
            ->whereDate('date', '>=', $date)
            ->whereDate('date', '<=', $window)
            ->orderBy('date')
            ->orderBy('time')
            ->get();
    }

    public function generateNextRound(Carbon $today): int
    {
        $created = 0;

        $laneIds = Lane::orderBy('id')->pluck('id')->all();

        foreach (League::with('teams')->get() as $league) {
            $upcoming = Fixture::where('league_id', $league->id)->where('status', 'upcoming')->get();

            if ($upcoming->count() >= 2 || $league->teams->count() < 2) {
                continue;
            }

            $latest = $upcoming->max('date');
            $nextDate = $latest ? Carbon::parse($latest)->addDay() : $today->copy()->addDay();

            $pairKey = fn ($a, $b) => min($a, $b) . '-' . max($a, $b);
            $playedKeys = Fixture::where('league_id', $league->id)
                ->get()
                ->map(fn (Fixture $f) => $pairKey($f->home_team_id, $f->away_team_id))
                ->all();

            $teams = $league->teams->shuffle()->values();
            $used = collect();
            $pairs = [];

            foreach ($teams as $team) {
                if ($used->contains($team->id)) {
                    continue;
                }

                $opponent = $teams->first(fn ($t) => $t->id !== $team->id && ! $used->contains($t->id) && ! in_array($pairKey($team->id, $t->id), $playedKeys, true));

                if (! $opponent) {
                    continue;
                }

                $used->push($team->id, $opponent->id);
                $pairs[] = [$team, $opponent];

                if (count($pairs) >= 2) {
                    break;
                }
            }

            foreach ($pairs as $index => [$home, $away]) {
                $laneId = $laneIds[$index % max(1, count($laneIds))] ?? null;

                Fixture::create([
                    'home_team_id' => $home->id,
                    'away_team_id' => $away->id,
                    'league_id' => $league->id,
                    'date' => $nextDate->copy()->addDays($index)->toDateString(),
                    'time' => '18:00',
                    'lane_id' => $laneId,
                    'status' => 'upcoming',
                ]);

                $created++;
            }
        }

        return $created;
    }

    public function resolveDueMatches(Carbon $date, array &$log): void
    {
        foreach ($this->dueMatches($date) as $fixture) {
            [$home, $away] = $this->scores($fixture);
            $liveHome = (int) round($home * (mt_rand(55, 75) / 100));
            $liveAway = (int) round($away * (mt_rand(55, 75) / 100));

            $fixture->home_score = max(0, $liveHome);
            $fixture->away_score = max(0, $liveAway);
            $fixture->status = 'live';
            $fixture->save();

            $log['matches']->push([
                'label' => $fixture->homeTeam->name . ' vs ' . $fixture->awayTeam->name,
                'status' => 'live',
                'home_score' => $fixture->home_score,
                'away_score' => $fixture->away_score,
            ]);
        }
    }

    public function resolveCompletedMatches(Carbon $date, array &$log): void
    {
        $live = Fixture::with(['homeTeam', 'awayTeam', 'league', 'lane'])
            ->where('status', 'live')
            ->whereDate('date', '<', $date)
            ->get();

        foreach ($live as $fixture) {
            if (! $this->isPrepared($fixture)) {
                $log['league_penalties']++;
                $log['reputation_delta'] = ($log['reputation_delta'] ?? 0) - 1;

                Complaint::create([
                    'visitor_id' => null,
                    'type' => 'league',
                    'description' => 'Match night not fully prepared (' . $fixture->homeTeam->name . ' vs ' . $fixture->awayTeam->name . ' on ' . $fixture->date->toDateString() . ').',
                    'status' => 'open',
                ]);
                $log['complaints_auto']++;
            }

            [$home, $away] = $this->scores($fixture);
            $fixture->home_score = max(0, (int) round($home));
            $fixture->away_score = max(0, (int) round($away));
            $fixture->status = 'completed';
            $fixture->save();

            $this->updateStandings($fixture);

            $this->releaseLane($fixture);

            $matchRevenue = $this->matchRevenue($fixture, $log);
            $log['revenue'] = ($log['revenue'] ?? 0) + $matchRevenue;
            $log['match_revenue'] = ($log['match_revenue'] ?? 0) + $matchRevenue;

            $log['matches']->push([
                'label' => $fixture->homeTeam->name . ' vs ' . $fixture->awayTeam->name,
                'status' => 'completed',
                'home_score' => $fixture->home_score,
                'away_score' => $fixture->away_score,
            ]);
        }
    }

    private function scores(Fixture $fixture): array
    {
        $home = $this->teamStrength($fixture->homeTeam);
        $away = $this->teamStrength($fixture->awayTeam);

        $homeScore = $home + ($home - $away) * 0.15 + mt_rand(-20, 20);
        $awayScore = $away + ($away - $home) * 0.15 + mt_rand(-20, 20);

        return [max(120, $homeScore), max(120, $awayScore)];
    }

    private function teamStrength(Team $team): float
    {
        $avg = (float) $team->members()->avg('average_score');

        return $avg > 0 ? $avg : 180.0;
    }

    private function updateStandings(Fixture $fixture): void
    {
        $home = $fixture->homeTeam;
        $away = $fixture->awayTeam;

        if ($fixture->home_score === $fixture->away_score) {
            $home->draws++;
            $away->draws++;
        } elseif ($fixture->home_score > $fixture->away_score) {
            $home->wins++;
            $away->losses++;
        } else {
            $away->wins++;
            $home->losses++;
        }

        $home->save();
        $away->save();
    }

    private function releaseLane(Fixture $fixture): void
    {
        if (! $fixture->lane_id) {
            return;
        }

        $lane = Lane::find($fixture->lane_id);
        if (! $lane) {
            return;
        }

        $lane->status = 'open';
        $lane->oil_level = max(20, $lane->oil_level - 30);
        $lane->save();
    }

    private function matchRevenue(Fixture $fixture, array $log): float
    {
        $prepared = $this->isPrepared($fixture);

        $revenue = 120.0;
        if ($prepared) {
            $revenue += 60.0;
        }

        if (! $prepared) {
            $revenue = round($revenue * 0.5, 2);
        }

        return $revenue;
    }
}
