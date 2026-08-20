<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\ClubConfig;
use App\Models\Lane;
use App\Models\Personality;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedClub();
        $this->seedPersonalities();
        $this->seedManualAccounts();
        $this->seedCaretakers();
        $this->seedCustomers();
        $this->seedLanes();

        $this->call([
            SiteContentSeeder::class,
            PortalContentSeeder::class,
            EventContentSeeder::class,
            ProShopSeeder::class,
            SnackbarSeeder::class,
            BowlingScoreSeeder::class,
            SimulationDataSeeder::class,
            InventoryCategoriesSeeder::class,
            LivedInDataSeeder::class,
        ]);
    }

    private function seedClub(): void
    {
        Club::updateOrCreate(
            ['slug' => 'cloud-nine-bowling'],
            [
                'name' => 'Cloud Nine Bowling',
                'description' => 'A premier ten-pin bowling club. Where every frame counts.',
                'total_lanes' => 12,
                'pro_shop_open' => true,
                'arcade_open' => true,
                'bar_open_hours' => '10:00:00',
                'bar_close_hours' => '23:00:00',
                'address' => '10 Pin Lane, Bowling District',
                'phone' => '+1 (555) 910-1010',
                'email' => 'info@cloudninebowling.com',
                'website' => 'http://localhost:8020',
            ]
        );

        ClubConfig::firstOrCreate(
            ['id' => 1],
            [
                'bad_day_mode' => false,
                'current_day' => 1,
                'reputation' => 75,
                'total_revenue' => 0,
                'total_expenses' => 0,
            ]
        );
    }

    private function seedPersonalities(): void
    {
        $personalities = [
            ['name' => 'honest', 'description' => 'Likely to confess when confronted, less likely to snitch falsely.'],
            ['name' => 'stoner', 'description' => 'Laid back, low accident chance but also low productivity, hard to anger.'],
            ['name' => 'overtly_friendly', 'description' => 'High energy, boosts nearby staff happiness, but may annoy others.'],
            ['name' => 'creepy', 'description' => 'Makes unsettling comments, other staff avoid them, higher chance of complaints.'],
            ['name' => 'nerd', 'description' => 'Works hard, can\'t stop talking about their interests, high competence but socially awkward.'],
            ['name' => 'rude', 'description' => 'Short with clients, higher complaint rate, but honest.'],
            ['name' => 'cliquey', 'description' => 'Forms alliances, doesn\'t like the player in particular, may snitch unfairly.'],
            ['name' => 'opportunistic', 'description' => 'Will snitch on others to get ahead, loyal only to themselves.'],
        ];

        foreach ($personalities as $p) {
            Personality::updateOrCreate(['name' => $p['name']], $p);
        }
    }

    private function seedManualAccounts(): void
    {
        $accounts = [
            ['name' => 'Admin', 'email' => 'siamkhandaker616@gmail.com', 'role' => 'admin', 'password' => 'password'],
            ['name' => 'Steward', 'email' => 'sadmarre@gmail.com', 'role' => 'steward', 'password' => 'password'],
            ['name' => 'Customer', 'email' => 'naturallyskyblue@gmail.com', 'role' => 'customer', 'password' => 'password'],
            ['name' => 'Caretaker', 'email' => 'siam.khandaker@g.bracu.ac.bd', 'role' => 'caretaker', 'password' => 'password'],
        ];

        foreach ($accounts as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                    'role' => $data['role'],
                    'email_verified_at' => now(),
                    'is_npc' => false,
                    'is_active' => true,
                ]
            );

            if (in_array($data['role'], ['admin', 'steward', 'caretaker'])) {
                Staff::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'role' => $data['role'] === 'admin' ? 'club_manager' : $data['role'],
                        'base_salary' => $data['role'] === 'admin' ? 5000 : ($data['role'] === 'steward' ? 3500 : 2500),
                        'current_salary' => $data['role'] === 'admin' ? 5000 : ($data['role'] === 'steward' ? 3500 : 2500),
                        'happiness' => 85,
                        'performance_score' => 75,
                        'honesty_score' => 80,
                        'hire_date' => Carbon::now()->subMonths(6),
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    private function seedCaretakers(): void
    {
        $caretakers = [
            ['name' => 'Jake Wheeler', 'email' => 'jake@cloudnine.ai', 'personalities' => ['honest']],
            ['name' => 'Mia Chen', 'email' => 'mia@cloudnine.ai', 'personalities' => ['nerd']],
            ['name' => 'Derek "Chill" Stone', 'email' => 'derek@cloudnine.ai', 'personalities' => ['stoner']],
            ['name' => 'Brittany Frost', 'email' => 'brittany@cloudnine.ai', 'personalities' => ['overtly_friendly']],
            ['name' => 'Marcus Vane', 'email' => 'marcus@cloudnine.ai', 'personalities' => ['creepy']],
            ['name' => 'Sully Park', 'email' => 'sully@cloudnine.ai', 'personalities' => ['rude']],
            ['name' => 'Tiffany Lux', 'email' => 'tiffany@cloudnine.ai', 'personalities' => ['cliquey']],
            ['name' => 'Ryan Cole', 'email' => 'ryan@cloudnine.ai', 'personalities' => ['opportunistic']],
            ['name' => 'Nadia Russo', 'email' => 'nadia@cloudnine.ai', 'personalities' => ['honest', 'overtly_friendly']],
            ['name' => 'Tommy Briggs', 'email' => 'tommy@cloudnine.ai', 'personalities' => ['stoner', 'nerd']],
            ['name' => 'Harper Voss', 'email' => 'harper@cloudnine.ai', 'personalities' => ['rude', 'opportunistic']],
            ['name' => 'Lily Tran', 'email' => 'lily@cloudnine.ai', 'personalities' => ['overtly_friendly', 'nerd']],
            ['name' => 'Damon Kirk', 'email' => 'damon@cloudnine.ai', 'personalities' => ['creepy', 'cliquey']],
            ['name' => 'Sadie Monroe', 'email' => 'sadie@cloudnine.ai', 'personalities' => ['honest', 'stoner']],
            ['name' => 'Eli Vance', 'email' => 'eli@cloudnine.ai', 'personalities' => ['cliquey', 'opportunistic']],
            ['name' => 'Roxy Dunn', 'email' => 'roxy@cloudnine.ai', 'personalities' => ['nerd', 'rude']],
        ];

        $happinessLevels = [65, 80, 90, 75, 45, 50, 55, 40, 85, 70, 35, 82, 30, 88, 42, 78];

        foreach ($caretakers as $i => $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'role' => 'caretaker',
                    'email_verified_at' => now(),
                    'is_npc' => true,
                ]
            );

            $staff = Staff::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'role' => 'caretaker',
                    'base_salary' => 2500,
                    'current_salary' => 2500,
                    'happiness' => $happinessLevels[$i],
                    'performance_score' => rand(40, 90),
                    'honesty_score' => rand(30, 95),
                    'hire_date' => Carbon::now()->subDays(rand(30, 365)),
                    'is_active' => true,
                ]
            );

            $personalityIds = Personality::whereIn('name', $data['personalities'])->pluck('id')->toArray();
            if ($personalityIds) {
                $staff->personalities()->syncWithoutDetaching($personalityIds);
            }
        }
    }

    private function seedCustomers(): void
    {
        $customers = [
            ['name' => 'Alice Morgan', 'tier' => 'premium'],
            ['name' => 'Bob Harper', 'tier' => 'regular'],
            ['name' => 'Charlie Nguyen', 'tier' => 'regular'],
            ['name' => 'Diana Reeves', 'tier' => 'premium'],
            ['name' => 'Ethan Brooks', 'tier' => 'regular'],
            ['name' => 'Fiona Gallagher', 'tier' => 'regular'],
            ['name' => 'George Lucas', 'tier' => 'regular'],
            ['name' => 'Hannah Lee', 'tier' => 'premium'],
        ];

        foreach ($customers as $data) {
            $slug = strtolower(str_replace(' ', '.', $data['name']));
            User::updateOrCreate(
                ['email' => $slug.'@cloudnine.ai'],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'role' => 'customer',
                    'email_verified_at' => now(),
                    'is_npc' => true,
                ]
            );
        }
    }

    private function seedLanes(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            Lane::updateOrCreate(
                ['lane_number' => $i],
                [
                    'status' => 'open',
                    'oil_level' => rand(70, 100),
                    'last_maintained_at' => Carbon::now()->subHours(rand(1, 24)),
                ]
            );
        }
    }
}
