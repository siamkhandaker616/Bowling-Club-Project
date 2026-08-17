<?php

namespace Database\Seeders;

use App\Models\BowlingScore;
use App\Models\Visitor;
use App\Services\Bowling\ScoringEngine;
use Illuminate\Database\Seeder;

class BowlingScoreSeeder extends Seeder
{
    public function run(): void
    {
        if (BowlingScore::exists()) {
            return;
        }

        $sampleFrames = [
            [
                [10], [10], [10], [10], [10],
                [10], [10], [10], [10], [10, 10, 10],
            ],
            [
                [10], [7, 3], [8, 0], [9, 1], [10],
                [10], [6, 2], [10], [7, 2], [9, 1, 10],
            ],
            [
                [9, 1], [8, 2], [7, 3], [6, 4], [5, 5],
                [10], [9, 0], [8, 1], [10], [7, 3, 9],
            ],
            [
                [6, 2], [5, 3], [4, 4], [3, 5], [10],
                [8, 2], [7, 1], [6, 2], [9, 0], [8, 1],
            ],
            [
                [10], [9, 1], [10], [8, 2], [7, 3],
                [10], [10], [9, 0], [8, 1], [10, 7, 2],
            ],
            [
                [5, 2], [4, 3], [6, 3], [7, 2], [8, 0],
                [9, 1], [6, 4], [10], [5, 3], [7, 2],
            ],
        ];

        $named = [
            ['Alice Morgan', 'alice.morgan@cloudnine.ai'],
            ['Diana Reeves', 'diana.reeves@cloudnine.ai'],
            ['Hannah Lee', 'hannah.lee@cloudnine.ai'],
        ];

        $rows = [];

        foreach ($named as $idx => [$name, $email]) {
            $visitor = Visitor::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'user_id' => null, 'tier' => 'premium', 'reputation_score' => 62]
            );

            $rows[] = [$visitor->id, $sampleFrames[$idx]];
        }

        foreach (array_slice($sampleFrames, 3) as $frames) {
            $rows[] = [null, $frames];
        }

        $daysBack = 0;

        foreach ($rows as [$visitorId, $frames]) {
            $framesData = array_map(
                fn (array $rolls) => ['rolls' => $rolls],
                $frames
            );

            $total = ScoringEngine::total($framesData);

            BowlingScore::create([
                'visitor_id' => $visitorId,
                'score' => $total,
                'frames_data' => $framesData,
                'played_at' => now()->subDays($daysBack++)->subMinutes(10),
                'is_high_score' => $total >= 200,
            ]);
        }
    }
}
