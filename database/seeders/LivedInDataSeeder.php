<?php

namespace Database\Seeders;

use App\Models\Accident;
use App\Models\BanRequest;
use App\Models\BookingQueue;
use App\Models\ClubConfig;
use App\Models\Complaint;
use App\Models\Confrontation;
use App\Models\Lane;
use App\Models\LaneBooking;
use App\Models\Staff;
use App\Models\StaffEvent;
use App\Models\StaffReview;
use App\Models\TouringRequest;
use App\Models\Visitor;
use App\Models\VisitorReview;
use Illuminate\Database\Seeder;

class LivedInDataSeeder extends Seeder
{
    public function run(): void
    {
        $visitors = Visitor::where('is_banned', false)->get();
        $staff = Staff::where('is_active', true)->get();
        $caretaStaff = $staff->filter(fn (Staff $s) => $s->role === 'caretaker')->values();
        $stewardStaff = $staff->filter(fn (Staff $s) => $s->role === 'steward')->values();
        $laneIds = Lane::pluck('id')->all();

        if ($visitors->isEmpty() || $staff->isEmpty()) {
            return;
        }

        $this->seedHistoricalBookings($visitors, $laneIds);
        $this->seedComplaints($visitors, $caretaStaff, $stewardStaff);
        $this->seedReviews($visitors, $caretaStaff);
        $this->seedBanRequests($visitors, $stewardStaff);
        $this->seedTouringRequests();
        $this->seedConfrontations($caretaStaff);
        $this->seedStaffEvents($caretaStaff);
        $this->seedAccidents($caretaStaff);
        $this->updateClubConfig();
    }

    private function seedHistoricalBookings($visitors, $laneIds): void
    {
        if (LaneBooking::count() > 20) {
            return;
        }

        $slots = ['morning', 'afternoon', 'evening'];
        $statuses = ['confirmed', 'completed', 'completed', 'completed', 'cancelled', 'pending'];
        $now = now();

        for ($daysAgo = 30; $daysAgo >= 1; $daysAgo--) {
            $date = $now->copy()->subDays($daysAgo);
            $count = mt_rand(4, 9);

            $dayVisitors = $visitors->shuffle()->take($count);

            foreach ($dayVisitors as $visitor) {
                $status = $daysAgo > 3 ? 'completed' : $statuses[array_rand($statuses)];

                LaneBooking::create([
                    'visitor_id' => $visitor->id,
                    'lane_id' => $laneIds[array_rand($laneIds)],
                    'date' => $date,
                    'time_slot' => $slots[array_rand($slots)],
                    'status' => $status,
                    'compensation_claimed' => false,
                ]);
            }
        }
    }

    private function seedComplaints($visitors, $caretaStaff, $stewardStaff): void
    {
        if (Complaint::count() > 5) {
            return;
        }

        $types = ['service', 'facility', 'food', 'staff_behavior', 'noise', 'cleanliness'];
        $compOptions = [null, null, 'free_game', 'refund', 'discount', 'apology', 'priority_queue'];
        $descriptions = [
            'Staff member was rude and dismissive when I asked for help.',
            'The lane was oiled poorly — ball kept hooking off the gutter.',
            'Waited 20 minutes for a simple drink order. Unacceptable.',
            'The restroom near Lane 7 has been dirty all evening.',
            'Music was way too loud, couldn\'t hear anyone talking.',
            'Shoes provided were two different sizes.',
            'Score machine glitched and lost our game data mid-frame.',
            'Bar staff forgot my order twice in a row.',
            'Found a crack in the return cushion — dangerous.',
            'Lane 3 pinsetter jammed three times during our session.',
            'Overcharged for the pro shop item, had to get a manager.',
            'The food was cold and stale when it arrived.',
        ];
        $resolutions = [
            'Issued a formal apology and compensation.',
            'Lane maintenance scheduled immediately.',
            'Staff member counseled on response time.',
            'Facilities team dispatched for deep clean.',
            'Volume levels adjusted across all zones.',
            'Replacement shoes provided immediately.',
            'Score data recovered from backup log.',
            'Bar staff reminded of order protocols.',
            'Cushion replaced, area inspected.',
            'Pinsetter serviced and recalibrated.',
            'Charge corrected, receipt reissued.',
            'Kitchen staff warned, food replaced.',
        ];

        $caretaUsers = $caretaStaff->pluck('id')->all();
        $stewardUsers = $stewardStaff->pluck('id')->all();

        foreach ($visitors->random(min(8, $visitors->count())) as $i => $visitor) {
            $daysAgo = mt_rand(1, 25);
            $resolved = $i < 6;
            $comp = $resolved ? $compOptions[array_rand($compOptions)] : null;

            Complaint::create([
                'visitor_id' => $visitor->id,
                'staff_id' => $caretaUsers ? $caretaUsers[array_rand($caretaUsers)] : null,
                'raised_by_staff_id' => $stewardUsers[array_rand($stewardUsers)] ?? null,
                'type' => $types[array_rand($types)],
                'description' => $descriptions[array_rand($descriptions)],
                'status' => $resolved ? 'resolved' : 'open',
                'resolution' => $resolved ? $resolutions[array_rand($resolutions)] : null,
                'compensation_type' => $comp,
                'resolved_by' => $resolved ? 1 : null,
                'resolved_at' => $resolved ? now()->subDays($daysAgo) : null,
                'created_at' => now()->subDays($daysAgo),
                'updated_at' => $resolved ? now()->subDays($daysAgo - 1) : now()->subDays($daysAgo),
            ]);
        }
    }

    private function seedReviews($visitors, $caretaStaff): void
    {
        if (StaffReview::count() > 5) {
            return;
        }

        $positiveBodies = [
            'Great service, very attentive and friendly.',
            'Helped me pick the right ball weight. Really knowledgable.',
            'Fast and professional. Will definitely come back.',
            'Made our group feel welcome. Top notch.',
            'Fixed the scoring issue in seconds. Impressed.',
        ];
        $negativeBodies = [
            'Seemed distracted and didn\'t finish the job.',
            'Was on their phone the whole time.',
            'Attitude was off, clearly didn\'t want to be here.',
        ];

        $completedBookings = LaneBooking::where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(20))
            ->get();

        foreach ($completedBookings->random(min(12, $completedBookings->count())) as $booking) {
            $good = mt_rand(1, 100) > 30;
            $rating = $good ? mt_rand(4, 5) : mt_rand(1, 3);

            StaffReview::create([
                'staff_id' => $caretaStaff->random()->id,
                'visitor_id' => $booking->visitor_id,
                'booking_id' => $booking->id,
                'rating' => $rating,
                'body' => $good ? $positiveBodies[array_rand($positiveBodies)] : $negativeBodies[array_rand($negativeBodies)],
                'was_polite' => $good || mt_rand(1, 100) > 50,
                'caused_issues' => ! $good && mt_rand(1, 100) > 40,
            ]);
        }

        foreach ($completedBookings->random(min(8, $completedBookings->count())) as $booking) {
            $good = mt_rand(1, 100) > 25;

            VisitorReview::create([
                'visitor_id' => $booking->visitor_id,
                'booking_id' => $booking->id,
                'rating' => $good ? mt_rand(4, 5) : mt_rand(2, 3),
                'body' => $good
                    ? ['Great bowler, clean game.', 'Fun to play with, kept score accurate.', 'Respectful of the lane and equipment.', 'Good energy, brought the group spirit up.'][array_rand([0, 1, 2, 3])]
                    : ['Took forever between frames.', 'Kept resetting pins manually, slowed the game.', 'Was loud and distracting to other lanes.'][array_rand([0, 1, 2])],
                'helpful_count' => mt_rand(0, 5),
                'not_helpful_count' => mt_rand(0, 2),
            ]);
        }
    }

    private function seedBanRequests($visitors, $stewardStaff): void
    {
        if (BanRequest::count() > 2) {
            return;
        }

        $reasons = [
            'Repeatedly disruptive behavior during evening sessions.',
            'Caught attempting to steal pro shop merchandise.',
            'Physical altercation with another visitor near Lane 9.',
            'Consistently refuses to follow lane etiquette rules.',
            'Found intoxicated and causing a scene, refused to leave.',
        ];

        $statuses = ['approved', 'approved', 'denied', 'pending'];
        $stewardIds = $stewardStaff->pluck('id')->all();

        foreach ($visitors->random(min(3, $visitors->count())) as $visitor) {
            $status = $statuses[array_rand($statuses)];

            BanRequest::create([
                'visitor_id' => $visitor->id,
                'requested_by_staff_id' => $stewardIds[array_rand($stewardIds)],
                'reason' => $reasons[array_rand($reasons)],
                'evidence' => 'Witnessed by multiple staff members.',
                'status' => $status,
                'reviewed_by_admin_id' => $status !== 'pending' ? 1 : null,
                'reviewed_at' => $status !== 'pending' ? now()->subDays(mt_rand(1, 10)) : null,
                'admin_notes' => match ($status) {
                    'approved' => 'Confirmed by reviewing footage.',
                    'denied' => 'Insufficient evidence. Will monitor.',
                    default => null,
                },
                'created_at' => now()->subDays(mt_rand(5, 20)),
            ]);
        }
    }

    private function seedTouringRequests(): void
    {
        if (TouringRequest::count() > 2) {
            return;
        }

        $teams = [
            ['team_name' => 'Dhaka Destroyers', 'home_club' => 'Strike Zone Dhaka', 'player_count' => 8],
            ['team_name' => 'Chittagong Chargers', 'home_club' => 'Pin Palace CHT', 'player_count' => 6],
            ['team_name' => 'Rajshahi Rockets', 'home_club' => 'Gutter Kings Rajshahi', 'player_count' => 10],
            ['team_name' => 'Sylhet Strikers', 'home_club' => 'Alley Cats Sylhet', 'player_count' => 7],
        ];

        $statuses = ['confirmed', 'confirmed', 'pending', 'declined', 'pending'];

        foreach ($teams as $i => $data) {
            $status = $statuses[$i % count($statuses)];

            TouringRequest::create([
                ...$data,
                'contact_email' => strtolower(str_replace(' ', '.', $data['team_name'])) . '@example.com',
                'arrival_date' => now()->addDays(mt_rand(5, 45)),
                'message' => 'We would love to visit and play a friendly match!',
                'status' => $status,
                'created_at' => now()->subDays(mt_rand(3, 15)),
            ]);
        }
    }

    private function seedConfrontations($caretaStaff): void
    {
        if (Confrontation::count() > 2) {
            return;
        }

        if ($caretaStaff->count() < 3) {
            return;
        }

        $incidentTypes = ['theft', 'substance_abuse', 'verbal_abuse', 'negligence', 'rule_violation'];
        $verdicts = ['upheld', 'dismissed', 'penalized'];
        $descriptions = [
            'Accused of taking food from the break room fridge without permission.',
            'Suspected of being under the influence during shift.',
            'Made inappropriate comments to a coworker.',
            'Left the maintenance area unlocked overnight.',
            'Repeatedly arriving late to scheduled shifts.',
        ];
        $responses = ['innocent', 'bs', 'innocent', 'confessed', 'bs'];
        $responseTexts = [
            'I would never do that. The fridge is shared.',
            'That\'s completely false. I was sober the entire time.',
            'It was just a joke, they took it the wrong way.',
            'I was called away for an emergency and forgot.',
            'Traffic has been terrible this week.',
        ];

        for ($i = 0; $i < 3 && $i < $caretaStaff->count() - 1; $i++) {
            $a = $caretaStaff[$i];
            $b = $caretaStaff[$i + 1];
            $verdict = $verdicts[array_rand($verdicts)];

            Confrontation::create([
                'reporter_staff_id' => $a->id,
                'accused_staff_id' => $b->id,
                'incident_type' => $incidentTypes[array_rand($incidentTypes)],
                'incident_description' => $descriptions[array_rand($descriptions)],
                'db_verified' => mt_rand(1, 100) > 40,
                'staff_response' => $responses[$i % count($responses)],
                'response_text' => $responseTexts[$i % count($responseTexts)],
                'investigation_result' => 'Reviewed by manager on duty.',
                'manager_verdict' => $verdict,
                'date' => now()->subDays(mt_rand(3, 20)),
                'happiness_impacts' => $verdict === 'penalized'
                    ? [$a->id => 3, $b->id => -8]
                    : ($verdict === 'upheld' ? [$b->id => -3] : []),
            ]);
        }
    }

    private function seedStaffEvents($caretaStaff): void
    {
        if (StaffEvent::count() > 5) {
            return;
        }

        $eventTypes = [
            'late_arrival' => ['Arrived 15 minutes late to shift.', -3, 'minor'],
            'early_leave' => ['Left shift 30 minutes early without approval.', -5, 'minor'],
            'great_service' => ['Received a glowing customer review.', 5, 'positive'],
            'breakage' => ['Accidentally broke a pin during reset.', -2, 'minor'],
            'overtime' => ['Stayed 2 hours past shift to cover emergency.', 8, 'positive'],
            'uniform_violation' => ['Not wearing required safety gear.', -2, 'minor'],
            'teamwork' => ['Helped another caretaker during a rush.', 4, 'positive'],
            'cleanliness_award' => ['Best lane maintenance record this month.', 6, 'positive'],
        ];

        foreach ($caretaStaff->random(min(10, $caretaStaff->count())) as $staff) {
            $type = array_rand($eventTypes);
            [$desc, $change, $severity] = $eventTypes[$type];

            StaffEvent::create([
                'staff_id' => $staff->id,
                'event_type' => $type,
                'severity' => $severity,
                'description' => $desc,
                'date' => now()->subDays(mt_rand(1, 20)),
                'happiness_change' => $change,
            ]);
        }
    }

    private function seedAccidents($caretaStaff): void
    {
        if (Accident::count() > 2) {
            return;
        }

        $types = ['oil_spill', 'pin_jam', 'ball_return_fault', 'lane_scuff', 'equipment_malfunction'];
        $severities = ['minor', 'minor', 'moderate', 'minor'];

        $shiftIds = \App\Models\Shift::pluck('id')->all();
        if (empty($shiftIds)) {
            return;
        }

        $bookings = LaneBooking::where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(15))
            ->get();

        for ($i = 0; $i < 4 && $i < $bookings->count(); $i++) {
            Accident::create([
                'staff_id' => $caretaStaff->random()->id,
                'shift_id' => $shiftIds[array_rand($shiftIds)],
                'type' => $types[array_rand($types)],
                'severity' => $severities[array_rand($severities)],
                'description' => 'Incident occurred during routine lane maintenance.',
                'resolved' => true,
                'resolution' => 'Area cleaned and inspected.',
                'affected_booking_id' => $bookings[$i]->id,
            ]);
        }
    }

    private function updateClubConfig(): void
    {
        $cfg = ClubConfig::singleton();
        $cfg->reputation = max($cfg->reputation, 72);
        $cfg->total_revenue = max((float) $cfg->total_revenue, 4250);
        $cfg->total_expenses = max((float) $cfg->total_expenses, 1800);
        $cfg->current_day = max($cfg->current_day, 1);
        $cfg->save();
    }
}
