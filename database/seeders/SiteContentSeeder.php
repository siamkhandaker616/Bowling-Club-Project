<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Club;
use App\Models\FacilityZone;
use Illuminate\Database\Seeder;

class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAnnouncements();
        $this->seedFacilityZones();
    }

    private function seedAnnouncements(): void
    {
        if (Announcement::where('title', 'Lane 7 Temporarily Closed for Oiling')->exists()) {
            return;
        }

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
                'body' => 'Enjoy 20% off all signature shakes and specialty coffees every Friday and Saturday from 5PM to 7PM. Cheers!',
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
                'title' => 'Snack Bar Closing Early Tonight',
                'body' => 'The snack bar will close at 9PM tonight due to a private event. Regular hours resume tomorrow.',
                'priority' => 'urgent',
                'is_active' => false,
                'published_at' => now()->subDays(3),
            ],
        ];

        foreach ($announcements as $data) {
            Announcement::create($data);
        }
    }

    private function seedFacilityZones(): void
    {
        if (FacilityZone::where('map_key', 'lanes')->exists()) {
            return;
        }

        $club = Club::first();
        if (!$club) {
            return;
        }

        $zones = [
            [
                'name' => 'Championship Lanes',
                'description' => 'Twelve championship ten-pin lanes with professional oiling, real-time status boards, and scoring monitors at every seat.',
                'open_time' => '10:00:00',
                'close_time' => '23:00:00',
                'facilities' => ['12 synthetic lanes', 'Real-time status displays', 'Shoe rental counter', 'Scoring monitors'],
                'map_key' => 'lanes',
                'sort_order' => 1,
            ],
            [
                'name' => 'Pro Shop',
                'description' => 'Custom ball drilling, expert fittings, and the full range of premium gear. Get your ball dialed in before you roll.',
                'open_time' => '10:00:00',
                'close_time' => '21:00:00',
                'facilities' => ['Custom ball drilling', 'Ball fitting', 'Accessory wall', 'Demo balls'],
                'map_key' => 'pro-shop',
                'sort_order' => 2,
            ],
            [
                'name' => 'Snack Bar',
                'description' => 'Fuel up between frames. Fresh smoothies, specialty coffees, and game-day bites served fast so you never miss your turn.',
                'open_time' => '10:00:00',
                'close_time' => '23:00:00',
                'facilities' => ['Smoothies & shakes', 'Specialty coffees', 'Game-day bites', 'Daily specials board'],
                'map_key' => 'snack-bar',
                'sort_order' => 3,
            ],
            [
                'name' => 'Arcade',
                'description' => 'A wall of retro cabinets and modern rhythm games. Trade your spare rolls for high scores and redemption prizes.',
                'open_time' => '11:00:00',
                'close_time' => '22:00:00',
                'facilities' => ['Retro cabinet row', 'Modern rhythm games', 'Prize redemption counter', 'Air hockey tables'],
                'map_key' => 'arcade',
                'sort_order' => 4,
            ],
            [
                'name' => 'Lounge',
                'description' => 'Kick back on leather sofas with big-screen sports. The perfect spot to relax between frames.',
                'open_time' => '12:00:00',
                'close_time' => '00:00:00',
                'facilities' => ['Leather sofas', 'Big-screen sports', 'Board game shelf', 'Charging stations'],
                'map_key' => 'lounge',
                'sort_order' => 5,
            ],
            [
                'name' => 'Restaurant',
                'description' => 'Full-service dining with private booths and a kids menu. Sit-down meals without leaving the club.',
                'open_time' => '11:00:00',
                'close_time' => '22:00:00',
                'facilities' => ['Full-service dining', 'Private booth area', 'Kids menu', 'Chef specials'],
                'map_key' => 'restaurant',
                'sort_order' => 6,
            ],
            [
                'name' => 'Parking',
                'description' => 'Free covered parking with EV charging, bike racks, and accessible spots right by the entrance.',
                'open_time' => '00:00:00',
                'close_time' => '23:59:59',
                'facilities' => ['Covered garage', 'EV charging', 'Bike rack', 'Accessible spots'],
                'map_key' => 'parking',
                'sort_order' => 7,
            ],
            [
                'name' => 'Washrooms',
                'description' => 'Clean, accessible facilities with a baby change station and a family restroom near the lanes.',
                'open_time' => '00:00:00',
                'close_time' => '23:59:59',
                'facilities' => ['Accessible facilities', 'Baby change station', 'Family restroom'],
                'map_key' => 'washrooms',
                'sort_order' => 8,
            ],
        ];

        foreach ($zones as $data) {
            FacilityZone::create(array_merge(['club_id' => $club->id, 'is_active' => true], $data));
        }
    }
}
