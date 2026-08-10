<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Database\Seeder;

class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $announcements = [
            [
                'title' => 'Lane 7 Temporarily Closed for Oiling',
                'body' => 'Lane 7 is undergoing emergency maintenance. Expected to reopen by tomorrow morning. We apologize for the inconvenience.',
                'priority' => 'urgent',
                'is_active' => true,
                'published_at' => now(),
            ],
            [
                'title' => 'Wednesday League Moved to 8PM',
                'body' => 'Due to high demand, the Wednesday night league has been rescheduled from 7PM to 8PM effective next week.',
                'priority' => 'normal',
                'is_active' => true,
                'published_at' => now(),
            ],
            [
                'title' => 'New Happy Hour Specials',
                'body' => 'Enjoy 20% off all craft beers every Friday and Saturday from 5PM to 7PM. Cheers!',
                'priority' => 'normal',
                'is_active' => true,
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Summer Tournament Registration Open',
                'body' => 'Sign up now for the Annual Summer Tournament! Prizes include trophies, gift cards, and bragging rights. Limited spots available.',
                'priority' => 'normal',
                'is_active' => true,
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Bar Closing Early Tonight',
                'body' => 'The bar will close at 9PM tonight due to a private event. Regular hours resume tomorrow.',
                'priority' => 'urgent',
                'is_active' => false,
                'published_at' => now()->subDays(3),
            ],
        ];

        foreach ($announcements as $data) {
            Announcement::create($data);
        }
    }
}
