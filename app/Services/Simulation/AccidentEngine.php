<?php

namespace App\Services\Simulation;

use App\Models\Accident;
use App\Models\Lane;
use App\Models\LaneBooking;
use App\Models\Shift;
use App\Models\StaffEvent;
use Carbon\Carbon;

class AccidentEngine
{
    public const ACCIDENT_TYPES = [
        'pinsetter_jam' => 'Pinsetter jam — lane stopped mid-frame',
        'oil_spill' => 'Oil spill on the approach — slip hazard',
        'shoe_issue' => 'Damaged rental shoe reported',
        'ball_damage' => 'Ball scuffed or dropped during carry',
        'walkway_incident' => 'Minor collision in the walkway',
    ];

    public const ACCIDENT_TYPES_BY_ROLE = [
        'caretaker' => [
            'pinsetter_jam' => 'Pinsetter jam — lane stopped mid-frame',
            'oil_spill' => 'Oil spill on the approach — slip hazard',
            'cleaning_issue' => 'Lane or rental shoes not cleaned between games',
            'discount_error' => 'Accidental discount applied to a non-entitled visitor',
            'ball_damage' => 'Ball scuffed or dropped during carry',
        ],
        'steward' => [
            'schedule_conflict' => 'Double-booked lane — schedule conflict',
            'missed_escalation' => 'Complaint escalation missed — response delayed',
            'walkway_incident' => 'Minor collision in the walkway',
        ],
        'club_manager' => [
            'miscommunication' => 'Internal miscommunication disrupted staff shifts',
            'supply_mishap' => 'Supply order mishandled — stock delayed',
        ],
    ];

    public const COSTS = [
        'minor' => 50,
        'moderate' => 150,
        'major' => 400,
    ];

    public const REPUTATION = [
        'minor' => -2,
        'moderate' => -4,
        'major' => -7,
    ];

    public function rollForDay(Carbon $date, array &$log): void
    {
        $cfg = \App\Models\ClubConfig::singleton();
        $badDay = $cfg->bad_day_mode;

        $shifts = Shift::with('staff', 'staff.personalities')->whereDate('date', $date)->get();

        $lowOilLanes = Lane::where('oil_level', '<', 20)->pluck('id')->all();

        foreach ($shifts as $shift) {
            $staff = $shift->staff;
            if (! $staff || ! $staff->is_active) {
                continue;
            }

            $chance = $this->baseChance($staff->role);

            $names = $staff->personalities->pluck('name')->all();
            foreach ($names as $name) {
                $chance += match ($name) {
                    'stoner' => -0.04,
                    'nerd' => -0.02,
                    'rude' => 0.03,
                    'creepy' => 0.03,
                    'cliquey' => 0.02,
                    'opportunistic' => 0.02,
                    default => 0,
                };
            }

            $chance += ((70 - $staff->happiness) / 100) * 0.05;

            if (in_array($shift->lane_id, $lowOilLanes, true)) {
                $chance += 0.05;
            }

            if ($badDay) {
                $chance = 1.0;
            } else {
                $chance = max(0.01, min(0.6, $chance));
            }

            if (mt_rand(1, 100) / 100 > $chance) {
                continue;
            }

            $roll = mt_rand(1, 100);
            $severity = $roll <= 60 ? 'minor' : ($roll <= 90 ? 'moderate' : 'major');

            $pools = self::ACCIDENT_TYPES_BY_ROLE[$staff->role] ?? self::ACCIDENT_TYPES;
            $type = array_rand($pools);

            $booking = LaneBooking::whereDate('date', $date)
                ->whereIn('status', ['confirmed', 'completed'])
                ->inRandomOrder()
                ->first();

            $accident = Accident::create([
                'staff_id' => $staff->id,
                'shift_id' => $shift->id,
                'type' => $type,
                'severity' => $severity,
                'description' => $pools[$type],
                'resolved' => false,
                'affected_booking_id' => $booking?->id,
            ]);

            $happinessHit = match ($severity) {
                'minor' => -2,
                'moderate' => -5,
                'major' => -10,
            };

            $staff->happiness = max(0, min(100, $staff->happiness + $happinessHit));
            $staff->performance_score = max(0, min(100, $staff->performance_score - ($severity === 'major' ? 5 : 2)));
            $staff->save();

            StaffEvent::create([
                'staff_id' => $staff->id,
                'event_type' => 'accident',
                'severity' => $severity,
                'description' => ucfirst(str_replace('_', ' ', $type)) . ' (' . $severity . ')',
                'date' => $date,
                'happiness_change' => $happinessHit,
            ]);

            $log['accidents']->push([
                'id' => $accident->id,
                'staff_name' => $staff->user->name ?? 'Staff',
                'type' => $type,
                'severity' => $severity,
                'description' => $pools[$type],
                'cost' => self::COSTS[$severity],
                'happiness_change' => $happinessHit,
            ]);

            if ($booking) {
                $booking->status = 'cancelled';
                $booking->save();
                $log['accidents_cancelled_bookings'] = ($log['accidents_cancelled_bookings'] ?? 0) + 1;
            }
        }
    }

    private function baseChance(string $role): float
    {
        return match ($role) {
            'caretaker' => 0.14,
            'steward' => 0.04,
            default => 0.02,
        };
    }
}
