<?php

namespace App\Services\Simulation;

use App\Models\BookingQueue;
use App\Models\ClubConfig;
use App\Models\Complaint;
use App\Models\Inventory;
use App\Models\LaneBooking;
use App\Models\Shift;
use App\Models\Staff;
use App\Models\StaffEvent;
use App\Models\StaffRelationship;
use App\Models\VisitorReview;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DayCycle
{
    public function __construct(
        private AccidentEngine $accidents,
        private InventoryService $inventory,
        private VisitorSpawner $spawner,
        private SocialEngine $social,
    ) {
    }

    public function advance(): array
    {
        $cfg = ClubConfig::singleton();
        $today = Clock::date();

        $log = [
            'date' => $today->toDateString(),
            'date_label' => $today->format('D, M j Y'),
            'bookings_created' => 0,
            'bookings_served' => 0,
            'queues_promoted' => 0,
            'revenue' => 0,
            'expenses' => 0,
            'accidents' => collect(),
            'low_stock' => collect(),
            'happiness_changes' => collect(),
            'complaints_auto' => 0,
            'reputation_delta' => 0,
            'quits' => 0,
            'relationship_changes' => collect(),
            'trash_talk' => [],
            'snitches' => [],
            'snitch_bonuses' => 0,
            'turnaways' => 0,
        ];

        $this->spawner->promoteQueues($today, $log);
        $this->serveToday($today, $log);
        $this->expireQueues($today, $log);

        $log['bookings_created'] = $this->spawner->runForDay($today->copy()->addDay(), $log);

        $this->accidents->rollForDay($today, $log);

        $this->happinessDrift($today, $log);
        $this->social->dailyDrift($today, $log);
        $this->inventory->dailyDecay($log);
        $this->autoComplaints($today, $log);

        $this->finance($today, $log);
        $this->updateReputation($log);

        $cfg->current_day = $cfg->current_day + 1;
        $cfg->save();

        $this->ensureSchedule(Clock::date());

        return $log;
    }

    private function serveToday(Carbon $date, array &$log): void
    {
        $bookings = LaneBooking::with('visitor')->whereDate('date', $date)->where('status', 'confirmed')->get();

        $impaired = $this->operationsImpaired();
        $capacity = $impaired ? (int) floor($bookings->count() * 0.7) : $bookings->count();

        foreach ($bookings as $index => $booking) {
            if ($index >= $capacity) {
                $booking->status = 'cancelled';
                $booking->compensation_claimed = true;
                $booking->compensation_type = 'free_game';
                $booking->save();

                $log['turnaways']++;
                $log['complaints_auto']++;
                continue;
            }

            $booking->status = 'completed';
            $booking->save();

            $price = ($booking->visitor->tier ?? 'regular') === 'premium' ? 25.0 : 15.0;
            $log['revenue'] += $price;
            $log['bookings_served']++;
        }
    }

    private function operationsImpaired(): bool
    {
        $shoes = Inventory::where('name', 'Bowling Shoes')->first();
        $pins = Inventory::where('name', 'Spare Pins')->first();

        return ($shoes && $shoes->quantity <= 0) || ($pins && $pins->quantity <= 0);
    }

    private function expireQueues(Carbon $date, array &$log): void
    {
        BookingQueue::whereDate('date', $date)->where('status', 'waiting')->update(['status' => 'expired']);
    }

    private function happinessDrift(Carbon $date, array &$log): void
    {
        $staff = Staff::with('personalities')->where('is_active', true)->get();
        $relationships = StaffRelationship::all();

        foreach ($staff as $member) {
            $delta = 0;
            $reasons = [];

            $names = $member->personalities->pluck('name')->all();
            foreach ($names as $name) {
                if ($name === 'stoner') {
                    $delta += 1;
                    $reasons[] = 'stoner (chill)';
                } elseif ($name === 'rude') {
                    $delta -= 1;
                    $reasons[] = 'rude (clashes)';
                }
            }

            $relScore = $relationships->filter(function ($rel) use ($member) {
                return (int) $rel->staff_a_id === (int) $member->id || (int) $rel->staff_b_id === (int) $member->id;
            })->sum('score');

            if ($relScore > 0) {
                $delta += 1;
                $reasons[] = 'good relationships';
            } elseif ($relScore < 0) {
                $delta -= 1;
                $reasons[] = 'bad relationships';
            }

            if ((float) $member->current_salary < (float) $member->base_salary) {
                $delta -= 1;
                $reasons[] = 'salary cut';
            }

            $delta += mt_rand(-1, 1);

            if ($delta !== 0) {
                $member->happiness = max(0, min(100, $member->happiness + $delta));
                $member->save();

                $log['happiness_changes']->push([
                    'staff_id' => $member->id,
                    'name' => $member->user->name ?? 'Staff',
                    'delta' => $delta,
                    'reason' => implode(', ', $reasons) ?: 'daily drift',
                ]);
            }

            if ($member->happiness <= 19 && mt_rand(1, 100) / 100 <= 0.5) {
                $this->quit($member, $date, $log);
            }
        }
    }

    private function quit(Staff $member, Carbon $date, array &$log): void
    {
        $member->is_active = false;
        $member->save();

        if ($member->user) {
            $member->user->is_active = false;
            $member->user->save();
        }

        StaffEvent::create([
            'staff_id' => $member->id,
            'event_type' => 'quit',
            'severity' => 'negative',
            'description' => 'Quit the club — happiness dropped to ' . $member->happiness . '.',
            'date' => $date,
            'happiness_change' => 0,
        ]);

        $log['quits']++;
        $log['happiness_changes']->push([
            'staff_id' => $member->id,
            'name' => $member->user->name ?? 'Staff',
            'delta' => 0,
            'reason' => 'QUIT the club (happiness ≤ 19)',
        ]);
    }

    private function autoComplaints(Carbon $date, array &$log): void
    {
        $completed = LaneBooking::with('visitor')
            ->whereDate('date', $date)
            ->where('status', 'completed')
            ->whereHas('visitor')
            ->get();

        foreach ($completed as $booking) {
            $risk = $booking->visitor->tier === 'premium' ? 0.1 : 0.06;

            if (mt_rand(1, 100) / 100 <= $risk) {
                Complaint::create([
                    'visitor_id' => $booking->visitor_id,
                    'type' => 'service',
                    'description' => 'Auto-reported service issue during booking on ' . $date->toDateString() . '.',
                    'status' => 'open',
                ]);
                $log['complaints_auto']++;
            }
        }
    }

    private function finance(Carbon $date, array &$log): void
    {
        $cfg = ClubConfig::singleton();

        $salaries = Staff::where('is_active', true)->sum('current_salary') / 30;

        $accidentCost = collect($log['accidents'])->sum(fn ($a) => $a['cost']);

        $expenses = $salaries + $accidentCost;

        $cfg->total_revenue = $cfg->total_revenue + $log['revenue'];
        $cfg->total_expenses = $cfg->total_expenses + $expenses;
        $cfg->save();

        $log['expenses'] = round($expenses, 2);
        $log['salaries'] = round($salaries, 2);
        $log['accident_cost'] = round($accidentCost, 2);
    }

    private function updateReputation(array &$log): void
    {
        $cfg = ClubConfig::singleton();
        $delta = 0;

        foreach ($log['accidents'] as $accident) {
            $delta += AccidentEngine::REPUTATION[$accident['severity']] ?? 0;
        }

        $openComplaints = Complaint::where('status', 'open')->count();
        $delta -= $openComplaints;

        $delta -= ($log['quits'] ?? 0) * 2;

        $avgHappiness = Staff::where('is_active', true)->avg('happiness');
        if ($avgHappiness !== null && $avgHappiness < 55) {
            $delta -= 1;
        }

        $reviews = VisitorReview::whereBetween('created_at', [Carbon::today(), now()])->get();
        $delta += $reviews->sum(fn ($r) => $r->rating >= 4 ? 1 : 0) - $reviews->count() * 0.5;

        $cfg->reputation = max(0, min(100, $cfg->reputation + $delta));
        $cfg->save();

        $log['reputation_delta'] = $delta;
    }

    public function ensureSchedule(Carbon $date): void
    {
        $slots = ['morning', 'afternoon', 'evening'];

        $staff = Staff::where('is_active', true)->get();

        foreach ($staff as $member) {
            if (Shift::where('staff_id', $member->id)->whereDate('date', $date)->exists()) {
                continue;
            }

            $slot = $slots[array_rand($slots)];
            $laneId = \App\Models\Lane::inRandomOrder()->value('id');

            Shift::create([
                'staff_id' => $member->id,
                'date' => $date,
                'time_slot' => $slot,
                'lane_id' => $laneId,
                'status' => 'scheduled',
            ]);
        }
    }

    public function markShiftComplete(Shift $shift): void
    {
        if ($shift->status === 'completed') {
            return;
        }

        $staff = $shift->staff;

        if ($staff) {
            $staff->happiness = max(0, min(100, $staff->happiness + 5));
            $staff->performance_score = max(0, min(100, $staff->performance_score + 2));
            $staff->save();

            StaffEvent::create([
                'staff_id' => $staff->id,
                'event_type' => 'worked',
                'severity' => 'positive',
                'description' => 'Completed shift: ' . ucfirst($shift->time_slot),
                'date' => $shift->date,
                'happiness_change' => 5,
            ]);
        }

        $shift->status = 'completed';
        $shift->save();
    }
}
