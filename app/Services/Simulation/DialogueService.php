<?php

namespace App\Services\Simulation;

use App\Models\Accident;
use App\Models\Inventory;
use App\Models\Staff;
use App\Models\StaffRelationship;
use Carbon\Carbon;

class DialogueService
{
    private const LINES = [
        'hostile' => [
            'speech' => ['I clock in, I do my job, I clock out.', 'Do not talk to me unless it is work.'],
            'thought' => ['Wonder how long this place keeps paying me...', 'The lanes are a mess, as usual.'],
            'exclamation' => ['This lane is a wreck again!', 'Did anyone fix anything this week?!'],
            'question' => ['Why am I always on the worst shift?', 'Who left the oil tank empty again?'],
        ],
        'neutral' => [
            'speech' => ['Shifts look normal for this week.', 'Keeping the lanes in decent shape.', 'Just restocking the usual supplies.'],
            'thought' => ['Routine day ahead.', 'Hope we get through the evening rush.', 'Nice weather outside, lucky for them.'],
            'exclamation' => ['Pinsetter hiccup on lane 6 — handled.', 'Rental shoes low again!'],
            'question' => ['Anyone know the closing checklist?', 'Is the ball polish on the shelf?', 'Who is covering the bar tonight?'],
        ],
        'friendly' => [
            'speech' => ['Good vibes around the break room lately.', 'The crew is solid, I will say that.', 'Lane 3 runs like a dream after the oil pass.'],
            'thought' => ['Might suggest a crew pizza night.', 'The evening crowd was fun to watch.', 'This place grows on you.'],
            'exclamation' => ['The pinsetter fix worked — nice!', 'Double check the back lanes before close!'],
            'question' => ['Up for a shift swap this weekend?', 'Got the maintenance schedule handy?'],
        ],
        'trusted' => [
            'speech' => ['Between us, management keeps changing the schedule last minute.', 'The bonuses are getting stingier, you notice?', 'If they cut the oil budget again the lanes will suffer.'],
            'thought' => ['That manager talk was out of line, but I will keep it quiet.', 'Everyone knows who carries this place.', 'Payday cannot come soon enough.'],
            'exclamation' => ['Another surprise inspection — again?!', 'They forgot the supply order, AGAIN!'],
            'question' => ['Did you hear what management said about layoffs?', 'Who is the manager mad at this week?'],
        ],
    ];

    public function bubblesFor(Staff $member, Carbon $date): array
    {
        $bubbles = [];
        $tone = $this->toneFor($member);

        if ($member->happiness <= 30) {
            $bubbles[] = ['type' => 'exclamation', 'text' => $this->pick($this->lines($tone, 'exclamation'))];
        } elseif ($member->happiness >= 75) {
            $bubbles[] = ['type' => 'speech', 'text' => $this->pick($this->lines($tone, 'speech'))];
        } else {
            $bubbles[] = ['type' => 'thought', 'text' => $this->pick($this->lines($tone, 'thought'))];
        }

        if (Accident::where('staff_id', $member->id)->whereDate('created_at', $date)->exists()) {
            $bubbles[] = ['type' => 'exclamation', 'text' => 'Not my day... the pinsetter jammed on my shift.'];
        }

        $lowOil = Inventory::where('name', 'Lane Oil')->value('quantity') ?? 0;
        if ($lowOil <= 8 && $member->happiness <= 60) {
            $bubbles[] = ['type' => 'question', 'text' => 'We are almost out of lane oil — how do we run a league tonight?'];
        }

        if (count($bubbles) < 2) {
            $bubbles[] = ['type' => 'question', 'text' => $this->pick($this->lines($tone, 'question'))];
        }

        return array_slice($bubbles, 0, 3);
    }

    private function toneFor(Staff $member): string
    {
        $rels = StaffRelationship::where('staff_a_id', $member->id)
            ->orWhere('staff_b_id', $member->id)
            ->get();

        if ($rels->isEmpty()) {
            return 'neutral';
        }

        $avg = $rels->avg('score');

        return match (true) {
            $avg >= 25 => 'trusted',
            $avg >= 8 => 'friendly',
            $avg > -8 => 'neutral',
            default => 'hostile',
        };
    }

    private function lines(string $tone, string $type): array
    {
        return self::LINES[$tone][$type] ?? self::LINES['neutral'][$type];
    }

    private function pick(array $lines): string
    {
        return $lines[array_rand($lines)];
    }
}
