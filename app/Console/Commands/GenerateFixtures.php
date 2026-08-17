<?php

namespace App\Console\Commands;

use App\Models\Fixture;
use App\Models\Lane;
use App\Models\League;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateFixtures extends Command
{
    protected $signature = 'fixtures:generate {--count=10 : Total fixtures to generate}';

    protected $description = 'Generate upcoming league fixtures';

    public function handle(): int
    {
        $count = (int) $this->option('count');
        $leagues = League::with('teams')->get()->filter(fn ($l) => $l->teams->count() >= 2);

        if ($leagues->isEmpty()) {
            $this->error('No leagues with 2+ teams found.');

            return Command::FAILURE;
        }

        $laneIds = Lane::orderBy('id')->pluck('id')->all();
        $created = 0;
        $nextDate = Carbon::now()->addDay();

        while ($created < $count) {
            $roundCreated = 0;

            foreach ($leagues as $league) {
                $playedKeys = Fixture::where('league_id', $league->id)
                    ->pluck('home_team_id')
                    ->zip(Fixture::where('league_id', $league->id)->pluck('away_team_id'))
                    ->map(fn ($pair) => min($pair[0], $pair[1]) . '-' . max($pair[0], $pair[1]))
                    ->values()
                    ->all();

                $teams = $league->teams->shuffle()->values();

                for ($i = 0; $i < $teams->count() - 1 && $created < $count; $i++) {
                    $team = $teams[$i];

                    for ($j = $i + 1; $j < $teams->count() && $created < $count; $j++) {
                        $opponent = $teams[$j];
                        $key = min($team->id, $opponent->id) . '-' . max($team->id, $opponent->id);

                        $alreadyUpcoming = Fixture::where('league_id', $league->id)
                            ->where('status', 'upcoming')
                            ->where(fn ($q) => $q
                                ->where(fn ($q2) => $q2->where('home_team_id', $team->id)->where('away_team_id', $opponent->id))
                                ->orWhere(fn ($q2) => $q2->where('home_team_id', $opponent->id)->where('away_team_id', $team->id))
                            )
                            ->exists();

                        if ($alreadyUpcoming) {
                            continue;
                        }

                        Fixture::create([
                            'home_team_id' => $team->id,
                            'away_team_id' => $opponent->id,
                            'league_id' => $league->id,
                            'date' => $nextDate->toDateString(),
                            'time' => '18:00',
                            'lane_id' => $laneIds[$created % max(1, count($laneIds))] ?? null,
                            'status' => 'upcoming',
                        ]);

                        $created++;
                        $roundCreated++;
                        $nextDate = $nextDate->copy()->addDay();
                    }
                }
            }

            if ($roundCreated === 0 || $nextDate->diffInDays(Carbon::now()) > 90) {
                break;
            }
        }

        $this->info("Generated {$created} fixtures.");

        return Command::SUCCESS;
    }
}
