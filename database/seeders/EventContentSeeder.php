<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Rsvp;
use Illuminate\Database\Seeder;

class EventContentSeeder extends Seeder
{
    public function run(): void
    {
        if (Event::where('title', 'Friday Night Strikes — Open Doubles')->exists()) {
            return;
        }

        $events = [
            [
                'title' => 'Friday Night Strikes — Open Doubles',
                'description' => 'Grab a partner and bowl double-format frames all night. Prizes for the highest pair total, plus a little something for the most dramatic split pick-up.',
                'date' => now()->addDays(3)->toDateString(),
                'time' => '19:30:00',
                'venue' => 'Main Lane Floor',
                'max_capacity' => 40,
                'price' => 0,
                'attendees' => 23,
            ],
            [
                'title' => 'The Tenth Frame Anniversary Bash',
                'description' => 'One year of the Tenth Frame! Open play, cake at 9, a retro scoreboard photo corner, and a sweepstakes for a season of league fees.',
                'date' => now()->addDays(6)->toDateString(),
                'time' => '18:00:00',
                'venue' => 'Club Lounge',
                'max_capacity' => 60,
                'price' => 0,
                'attendees' => 18,
            ],
            [
                'title' => 'Summer Showdown Tournament',
                'description' => 'The season\'s big one. Bracket play across the full lane floor, seeded by league average. Ticket covers lane time, shoes, and a competitor T-shirt.',
                'date' => now()->addDays(10)->toDateString(),
                'time' => '10:00:00',
                'venue' => 'Main Lane Floor',
                'max_capacity' => 24,
                'price' => 1500,
                'attendees' => 9,
            ],
            [
                'title' => 'Learn-to-Bowl Night',
                'description' => 'New to the sport? Coaches walk you through stance, swing, and the glorious math of a strike. All gear provided.',
                'date' => now()->subDays(5)->toDateString(),
                'time' => '17:00:00',
                'venue' => 'Lanes 9–12',
                'max_capacity' => 30,
                'price' => 0,
                'attendees' => 12,
            ],
        ];

        $names = [
            'Arif Hassan', 'Bijoy Das', 'Camila Reyes', 'David Chen', 'Eva Müller',
            'Farhan Islam', 'Grace Okafor', 'Hiro Tanaka', 'Irene Silva', 'Jamal Reed',
            'Kavya Sharma', 'Leo Rossi', 'Maya Singh', 'Noah Kim', 'Olivia Brown',
            'Priya Patel', 'Quinn Davis', 'Rafael Costa', 'Sara Ahmed', 'Tom Wilson',
            'Uma Thapa', 'Victor Nguyen', 'Wendy Zhao', 'Xavier Jones', 'Yara Ali',
            'Zack Miller', 'Aisha Bello', 'Brandon Lee', 'Clara Johansson', 'Daniel Park',
            'Emily Clark', 'Faisal Noor', 'Gabriela Flores', 'Hassan Ali', 'Ingrid Svensson',
            'Jordan Taylor', 'Keiko Sato', 'Liam O\'Brien', 'Maria Garcia', 'Nathan Scott',
        ];

        foreach ($events as $index => $data) {
            $attendees = $data['attendees'];
            unset($data['attendees']);

            $event = Event::create($data);

            for ($i = 0; $i < $attendees; $i++) {
                $name = $names[($index * 7 + $i) % count($names)];
                $email = strtolower(str_replace([' ', '\''], ['.', ''], $name))
                    . ($index + 1) . $i . '@example.com';

                $rsvp = Rsvp::create([
                    'event_id' => $event->id,
                    'visitor_name' => $name,
                    'visitor_email' => $email,
                    'status' => 'confirmed',
                ]);

                if ($data['price'] > 0) {
                    $rsvp->payment()->create([
                        'transaction_id' => 'TF' . strtoupper(substr(md5($name . $email . $i), 0, 14)),
                        'amount' => $data['price'],
                        'currency' => 'BDT',
                        'status' => 'success',
                        'customer_name' => $name,
                        'customer_email' => $email,
                        'paid_at' => now()->subDays(rand(1, 8))->subMinutes(rand(0, 1440)),
                    ]);
                }
            }

            $event->update(['current_rsvps' => $event->rsvps()->count()]);
        }
    }
}
