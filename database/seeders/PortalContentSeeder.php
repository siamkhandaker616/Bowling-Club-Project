<?php

namespace Database\Seeders;

use App\Models\Fixture;
use App\Models\Lane;
use App\Models\League;
use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Database\Seeder;

class PortalContentSeeder extends Seeder
{
    public function run(): void
    {
        if (League::where('name', 'Premier Bowling League')->exists()) {
            return;
        }

        $lanes = Lane::pluck('id');

        $pbl = League::create(['name' => 'Premier Bowling League', 'season' => 'Summer 2026']);
        $ccc = League::create(['name' => 'Corporate Challenge Cup', 'season' => 'Summer 2026']);

        $pblTeams = collect([
            ['name' => 'Thunder Rollers', 'wins' => 8, 'losses' => 2, 'draws' => 1],
            ['name' => 'Pin Crushers', 'wins' => 7, 'losses' => 3, 'draws' => 1],
            ['name' => 'Gutter Kings', 'wins' => 5, 'losses' => 5, 'draws' => 1],
            ['name' => 'Strike Squad', 'wins' => 6, 'losses' => 4, 'draws' => 1],
            ['name' => 'Lane Legends', 'wins' => 4, 'losses' => 6, 'draws' => 1],
        ])->map(fn ($t) => Team::create(array_merge($t, ['league_id' => $pbl->id])));

        $cccTeams = collect([
            ['name' => 'Tech Titans', 'wins' => 9, 'losses' => 1, 'draws' => 0],
            ['name' => 'Finance Aces', 'wins' => 6, 'losses' => 4, 'draws' => 0],
            ['name' => 'Legal Sparks', 'wins' => 5, 'losses' => 5, 'draws' => 0],
            ['name' => 'Media Mavericks', 'wins' => 3, 'losses' => 7, 'draws' => 0],
            ['name' => 'Health Hawks', 'wins' => 7, 'losses' => 3, 'draws' => 0],
        ])->map(fn ($t) => Team::create(array_merge($t, ['league_id' => $ccc->id])));

        $allTeams = $pblTeams->concat($cccTeams);

        $memberNames = [
            'Arif Hassan', 'Bijoy Das', 'Camila Reyes', 'David Chen', 'Eva Müller',
            'Farhan Islam', 'Grace Okafor', 'Hiro Tanaka', 'Irene Silva', 'Jamal Reed',
            'Kavya Sharma', 'Leo Rossi', 'Maya Singh', 'Noah Kim', 'Olivia Brown',
            'Priya Patel', 'Quinn Davis', 'Rafael Costa', 'Sara Ahmed', 'Tom Wilson',
            'Uma Thapa', 'Victor Nguyen', 'Wendy Zhao', 'Xavier Jones', 'Yara Ali',
            'Zack Miller', 'Aisha Bello', 'Brandon Lee', 'Clara Johansson', 'Daniel Park',
            'Emily Clark', 'Faisal Noor', 'Gabriela Flores', 'Hassan Ali', 'Ingrid Svensson',
            'Jordan Taylor', 'Keiko Sato', 'Liam O\'Brien', 'Maria Garcia', 'Nathan Scott',
        ];

        $allTeams->each(function ($team) use ($memberNames) {
            $count = rand(3, 5);
            $selected = collect($memberNames)->shuffle()->take($count);
            $selected->each(fn ($name) => TeamMember::create([
                'team_id' => $team->id,
                'name' => $name,
                'average_score' => rand(120, 260) + (rand(0, 99) / 100),
            ]));
        });

        $completedFixtures = [
            ['home' => 0, 'away' => 1, 'home_score' => 245, 'away_score' => 218],
            ['home' => 2, 'away' => 3, 'home_score' => 198, 'away_score' => 201],
            ['home' => 4, 'away' => 0, 'home_score' => 187, 'away_score' => 232],
            ['home' => 5, 'away' => 6, 'home_score' => 276, 'away_score' => 254],
            ['home' => 7, 'away' => 8, 'home_score' => 201, 'away_score' => 195],
        ];

        $liveFixtures = [
            ['home' => 1, 'away' => 3, 'home_score' => 178, 'away_score' => 165],
        ];

        $upcomingFixtures = [
            ['home' => 0, 'away' => 5, 'days' => 3],
            ['home' => 6, 'away' => 9, 'days' => 5],
            ['home' => 2, 'away' => 4, 'days' => 7],
            ['home' => 8, 'away' => 1, 'days' => 10],
        ];

        $laneIndex = 0;

        foreach ($completedFixtures as $f) {
            $home = $allTeams[$f['home']];
            $away = $allTeams[$f['away']];
            Fixture::create([
                'home_team_id' => $home->id,
                'away_team_id' => $away->id,
                'date' => now()->subDays(rand(1, 14)),
                'time' => sprintf('%02d:%02d', rand(14, 21), [0, 30][rand(0, 1)]),
                'lane_id' => $lanes[$laneIndex++ % $lanes->count()],
                'league_id' => $home->league_id,
                'home_score' => $f['home_score'],
                'away_score' => $f['away_score'],
                'status' => 'completed',
            ]);
        }

        foreach ($liveFixtures as $f) {
            $home = $allTeams[$f['home']];
            $away = $allTeams[$f['away']];
            Fixture::create([
                'home_team_id' => $home->id,
                'away_team_id' => $away->id,
                'date' => now()->toDateString(),
                'time' => now()->subHour()->format('H:i'),
                'lane_id' => $lanes[$laneIndex++ % $lanes->count()],
                'league_id' => $home->league_id,
                'home_score' => $f['home_score'],
                'away_score' => $f['away_score'],
                'status' => 'live',
            ]);
        }

        foreach ($upcomingFixtures as $f) {
            $home = $allTeams[$f['home']];
            $away = $allTeams[$f['away']];
            Fixture::create([
                'home_team_id' => $home->id,
                'away_team_id' => $away->id,
                'date' => now()->addDays($f['days'])->toDateString(),
                'time' => sprintf('%02d:%02d', rand(14, 21), [0, 30][rand(0, 1)]),
                'lane_id' => $lanes[$laneIndex++ % $lanes->count()],
                'league_id' => $home->league_id,
                'status' => 'upcoming',
            ]);
        }
    }
}
