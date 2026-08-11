<?php

namespace App\Services\Simulation;

use App\Models\BookingQueue;
use App\Models\ClubConfig;
use App\Models\Lane;
use App\Models\LaneBooking;
use App\Models\Visitor;
use Carbon\Carbon;

class VisitorSpawner
{
    public function runForDay(Carbon $date, array &$log): int
    {
        $cfg = ClubConfig::singleton();
        $created = 0;

        $visitors = Visitor::where('is_banned', false)->get();
        $slots = array_keys(Clock::timeSlots());
        $chance = $cfg->bad_day_mode ? 0.4 : 0.65;

        foreach ($visitors as $visitor) {
            if (mt_rand(1, 100) / 100 > $chance) {
                continue;
            }

            $slot = $slots[array_rand($slots)];
            $lane = $this->pickFreeLane($date, $slot);

            $booking = LaneBooking::create([
                'visitor_id' => $visitor->id,
                'lane_id' => $lane ? $lane->id : null,
                'date' => $date,
                'time_slot' => $slot,
                'status' => $lane ? 'confirmed' : 'pending',
                'queue_position' => $lane ? null : BookingQueue::whereDate('date', $date)->max('position') + 1,
            ]);

            if (! $lane) {
                BookingQueue::create([
                    'booking_id' => $booking->id,
                    'visitor_id' => $visitor->id,
                    'lane_id' => 1,
                    'date' => $date,
                    'time_slot' => $slot,
                    'position' => $booking->queue_position,
                    'status' => 'waiting',
                ]);
            }

            $created++;
        }

        return $created;
    }

    public function pickFreeLane(Carbon $date, string $slot): ?Lane
    {
        $takenLaneIds = LaneBooking::whereDate('date', $date)
            ->where('time_slot', $slot)
            ->where('status', 'confirmed')
            ->pluck('lane_id')
            ->all();

        return Lane::where('status', 'open')
            ->whereNotIn('id', $takenLaneIds)
            ->inRandomOrder()
            ->first();
    }

    public function promoteQueues(Carbon $date, array &$log): void
    {
        $queues = BookingQueue::with('booking')
            ->whereDate('date', $date)
            ->where('status', 'waiting')
            ->orderBy('position')
            ->get();

        foreach ($queues as $entry) {
            $lane = $this->pickFreeLane($date, $entry->time_slot);
            if (! $lane) {
                break;
            }

            $booking = $entry->booking;
            if ($booking && $booking->status === 'pending') {
                $booking->lane_id = $lane->id;
                $booking->status = 'confirmed';
                $booking->queue_position = null;
                $booking->save();

                $entry->status = 'notified';
                $entry->lane_id = $lane->id;
                $entry->save();

                $log['queues_promoted'] = ($log['queues_promoted'] ?? 0) + 1;
            }
        }
    }
}
