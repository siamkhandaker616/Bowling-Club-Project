<?php

namespace App\Services\Simulation;

use App\Models\BookingQueue;
use App\Models\ClubConfig;
use App\Models\Complaint;
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
        ];

        $this->serveToday($today, $log);
        $this->expireQueues($today, $log);
        $this->spawner->promoteQueues($today, $log);

        $log['bookings_created'] = $this->spawner->runForDay($today->copy()->addDay(), $log);

        $this->accidents->rollForDay($today, $log);

        $this->happinessDrift($today, $log);
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

        foreach ($bookings as $booking) {
            $booking->status = 'completed';
            $booking->save();

            $price = ($booking->visitor->tier ?? 'regular') === 'premium' ? 25.0 : 15.0;
            $log['revenue'] += $price;
            $log['bookings_served']++;
        }
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
        }
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
        if ($shift->status !== 'completed') {
            $shift->status = 'completed';
            $shift->save();
        }
    }
}
