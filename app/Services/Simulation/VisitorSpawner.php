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

            if ($lane) {
                LaneBooking::create([
                    'visitor_id' => $visitor->id,
                    'lane_id' => $lane->id,
                    'date' => $date,
                    'time_slot' => $slot,
                    'status' => 'confirmed',
                ]);
            } else {
                $this->queueVisitor($visitor, $date, $slot);
            }

            $created++;
        }

        return $created;
    }

    private function queueVisitor(Visitor $visitor, Carbon $date, string $slot): void
    {
        $targetLane = Lane::orderBy('id')->first();

        if (! $targetLane) {
            return;
        }

        $position = (BookingQueue::whereDate('date', $date)->max('position') ?? 0) + 1;

        $booking = LaneBooking::create([
            'visitor_id' => $visitor->id,
            'lane_id' => $targetLane->id,
            'date' => $date,
            'time_slot' => $slot,
            'status' => 'pending',
            'queue_position' => $position,
        ]);

        BookingQueue::create([
            'booking_id' => $booking->id,
            'visitor_id' => $visitor->id,
            'lane_id' => $targetLane->id,
            'date' => $date,
            'time_slot' => $slot,
            'position' => $position,
            'status' => 'waiting',
        ]);
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

    public function promoteForSlot(Carbon $date, string $slot, array &$log): void
    {
        $entry = BookingQueue::with('booking')
            ->whereDate('date', $date)
            ->where('time_slot', $slot)
            ->where('status', 'waiting')
            ->orderBy('position')
            ->first();

        if (! $entry) {
            return;
        }

        $lane = $this->pickFreeLane($date, $slot);
        if (! $lane) {
            return;
        }

        $this->promoteEntry($entry, $lane, $log);
    }

    public function promoteQueues(Carbon $date, array &$log): void
    {
        $queues = BookingQueue::with('booking')
            ->leftJoin('visitors', 'booking_queues.visitor_id', '=', 'visitors.id')
            ->whereDate('booking_queues.date', $date)
            ->where('booking_queues.status', 'waiting')
            ->orderByDesc('visitors.reputation_score')
            ->orderBy('booking_queues.position')
            ->select('booking_queues.*')
            ->get();

        foreach ($queues as $entry) {
            $lane = $this->pickFreeLane($date, $entry->time_slot);
            if (! $lane) {
                continue;
            }

            $this->promoteEntry($entry, $lane, $log);
        }
    }

    private function promoteEntry(BookingQueue $entry, Lane $lane, array &$log): void
    {
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
