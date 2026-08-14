<?php

namespace App\Services\Simulation;

use App\Models\Bonus;
use App\Models\Confrontation;
use App\Models\Shift;
use App\Models\Staff;
use App\Models\StaffEvent;
use App\Models\StaffRelationship;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SocialEngine
{
    public function dailyDrift(Carbon $date, array &$log): void
    {
        $staff = Staff::with('personalities', 'user')->where('is_active', true)->get();

        if ($staff->count() < 2) {
            return;
        }

        $accidentStaffIds = collect($log['accidents'] ?? [])->pluck('staff_id')->all();
        $todayShiftStaff = Shift::whereDate('date', $date)->pluck('staff_id')->all();

        foreach ($staff as $member) {
            foreach ($staff as $other) {
                if ((int) $member->id >= (int) $other->id) {
                    continue;
                }
                if (! in_array((int) $member->id, $todayShiftStaff, true) || ! in_array((int) $other->id, $todayShiftStaff, true)) {
                    continue;
                }
                $this->driftPair($member, $other, $accidentStaffIds, $date, $log);
            }
        }

        $this->npcVenting($staff, $date, $log);
        $this->snitchRoll($date, $log);
    }

    public function vent(Staff $member, Carbon $date, array &$log): void
    {
        $line = $this->ventLine($member);

        StaffEvent::create([
            'staff_id' => $member->id,
            'event_type' => 'trash_talk',
            'severity' => null,
            'description' => 'Trash-talked about management around the break room: "' . $line . '"',
            'date' => $date,
            'happiness_change' => 2,
        ]);

        $member->happiness = max(0, min(100, $member->happiness + 2));
        $member->save();

        $log['trash_talk'][] = [
            'staff_id' => $member->id,
            'name' => $member->user->name ?? 'Staff',
            'line' => $line,
        ];

        $this->snitchRoll($date, $log, (int) $member->id);
    }

    private function driftPair(Staff $a, Staff $b, array $accidentStaffIds, Carbon $date, array &$log): void
    {
        $aId = (int) $a->id;
        $bId = (int) $b->id;

        $rel = StaffRelationship::where(function ($q) use ($aId, $bId) {
            $q->where('staff_a_id', $aId)->where('staff_b_id', $bId);
        })->orWhere(function ($q) use ($aId, $bId) {
            $q->where('staff_a_id', $bId)->where('staff_b_id', $aId);
        })->first();

        if (! $rel) {
            $rel = StaffRelationship::create([
                'staff_a_id' => min($aId, $bId),
                'staff_b_id' => max($aId, $bId),
                'level' => 'neutral',
                'score' => 0,
            ]);
        }

        $delta = 1;

        if (in_array($aId, $accidentStaffIds, true) || in_array($bId, $accidentStaffIds, true)) {
            $delta -= 2;
        }

        foreach ([$a, $b] as $member) {
            if ($member->happiness > 75) {
                $delta += 1;
            } elseif ($member->happiness < 30) {
                $delta -= 1;
            }
        }

        $rel->score = max(-100, min(100, $rel->score + $delta));

        $newLevel = $this->levelFor($rel->score);
        if ($newLevel !== $rel->level) {
            $other = (int) $a->id === $aId ? $b : $a;

            $log['relationship_changes']->push([
                'a' => $a->user->name ?? 'Staff ' . $aId,
                'b' => $b->user->name ?? 'Staff ' . $bId,
                'from' => $rel->level,
                'to' => $newLevel,
            ]);

            StaffEvent::create([
                'staff_id' => $aId,
                'event_type' => 'social',
                'severity' => null,
                'description' => 'Relationship with ' . ($other->user->name ?? 'coworker') . " shifted {$rel->level} → {$newLevel}.",
                'date' => $date,
                'happiness_change' => 0,
            ]);
        }

        $rel->level = $newLevel;
        $rel->save();
    }

    private function npcVenting(Collection $staff, Carbon $date, array &$log): void
    {
        foreach ($staff as $member) {
            if ($member->user && $member->user->is_npc === false) {
                continue;
            }
            if ($member->happiness >= 45) {
                continue;
            }
            if (mt_rand(1, 100) / 100 > 0.25) {
                continue;
            }

            StaffEvent::create([
                'staff_id' => $member->id,
                'event_type' => 'trash_talk',
                'severity' => null,
                'description' => 'Trash-talked about management around the break room: "' . $this->ventLine($member) . '"',
                'date' => $date,
                'happiness_change' => 0,
            ]);

            $log['trash_talk'][] = [
                'staff_id' => $member->id,
                'name' => $member->user->name ?? 'Staff',
                'line' => $this->ventLine($member),
            ];
        }
    }

    private function snitchRoll(Carbon $date, array &$log, ?int $onlyStaffId = null): void
    {
        $vents = StaffEvent::whereDate('date', $date)
            ->where('event_type', 'trash_talk')
            ->when($onlyStaffId, fn ($q, $id) => $q->where('staff_id', $id))
            ->get();

        foreach ($vents as $vent) {
            $trashTalker = Staff::with('user')->find($vent->staff_id);
            if (! $trashTalker) {
                continue;
            }

            $snitches = Staff::with('personalities', 'user')
                ->where('is_active', true)
                ->where('id', '!=', $trashTalker->id)
                ->get();

            foreach ($snitches as $snitch) {
                $chance = 0.06;
                foreach ($snitch->personalities->pluck('name')->all() as $name) {
                    $chance += match ($name) {
                        'creepy' => 0.25,
                        'cliquey' => 0.12,
                        'opportunistic' => 0.1,
                        'honest' => -0.15,
                        'stoner' => -0.12,
                        'nerd' => 0.05,
                        'rude' => 0.02,
                        default => 0,
                    };
                }

                if (mt_rand(1, 100) / 100 > min(0.6, max(0.02, $chance))) {
                    continue;
                }

                $confrontation = Confrontation::create([
                    'reporter_staff_id' => $snitch->id,
                    'accused_staff_id' => $trashTalker->id,
                    'incident_type' => 'other',
                    'incident_description' => 'Overheard ' . ($trashTalker->user->name ?? 'a coworker') . ' trash-talking management: "' . $vent->description . '"',
                    'db_verified' => true,
                    'date' => $date,
                    'happiness_impacts' => [],
                ]);

                $this->snitchReward($snitch, $date, $log);

                $log['snitches'][] = [
                    'snitch' => $snitch->user->name ?? 'Staff',
                    'target' => $trashTalker->user->name ?? 'Staff',
                    'confrontation_id' => $confrontation->id,
                ];
            }
        }
    }

    private function snitchReward(Staff $snitch, Carbon $date, array &$log): void
    {
        $snitch->happiness = max(0, min(100, $snitch->happiness + 5));
        $snitch->save();

        Bonus::create([
            'staff_id' => $snitch->id,
            'type' => 'recognition',
            'reason' => 'Reported disloyalty among the crew',
            'amount_or_hours' => 0,
            'date' => $date,
            'issued_by' => null,
        ]);

        StaffEvent::create([
            'staff_id' => $snitch->id,
            'event_type' => 'bonus',
            'severity' => 'positive',
            'description' => 'Snitch bonus for reporting a coworker venting',
            'date' => $date,
            'happiness_change' => 5,
        ]);

        $log['snitch_bonuses'] = ($log['snitch_bonuses'] ?? 0) + 1;
    }

    private function ventLine(Staff $member): string
    {
        $lines = [
            'The manager is running this place into the ground.',
            'Nobody here respects the people who actually do the work.',
            'Pay is a joke and the schedule is worse.',
            'I heard management is slashing bonuses again next month.',
            'One more surprise inspection and I am walking out.',
        ];

        $personalities = $member->personalities->pluck('name')->all();

        if (in_array('stoner', $personalities, true)) {
            $lines[] = 'Like, the oil machine is the least of our problems, man.';
        }
        if (in_array('rude', $personalities, true)) {
            $lines[] = 'Management can screw off. They have no idea what they are doing.';
        }
        if (in_array('cliquey', $personalities, true)) {
            $lines[] = 'The shift leads look out for their own and leave us hanging.';
        }

        return $lines[array_rand($lines)];
    }

    private function levelFor(int $score): string
    {
        return match (true) {
            $score >= 25 => 'trusted',
            $score >= 8 => 'friendly',
            $score > -8 => 'neutral',
            default => 'hostile',
        };
    }
}
