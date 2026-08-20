<?php

namespace App\Services\Simulation;

use App\Models\Confrontation;
use App\Models\Staff;
use App\Models\StaffMessage;
use Illuminate\Support\Collection;

class InterrogationEngine
{
    private const OPENERS = [
        'verified' => [
            'honest' => ['I saw the log before you did... no point hiding it.', 'I was going to come clean about it myself.', 'The records are right. I messed up.'],
            'neutral' => ['...You pulled the records, did you?', 'Yeah, I heard the report. What do you want me to say?', 'So this is about the report. Fine.'],
            'liar' => ['I do not know what record you are looking at.', 'Whoever wrote that report has it out for me.', 'That log is wrong. Ask anyone on my shift.'],
        ],
        'unverified' => [
            'honest' => ['I had nothing to do with it, and I will tell you straight what I saw.', 'I heard about the report. I was nowhere near it.', 'I will be honest — I do not trust whoever filed that.'],
            'neutral' => ['I was working my shift like always.', 'Somebody is fishing for trouble.', 'Ask around. I was where I was supposed to be.'],
            'liar' => ['I have no idea what this is about.', 'People talk a lot around here. I would not listen.', 'This is a joke, right?'],
        ],
    ];

    private const ANSWERS = [
        'where' => [
            'verified' => [
                'honest' => ['Lane 6, the same as it says. I was the only one back there.', 'That shift, my lane, my name on the rack sheet. Yeah.'],
                'neutral' => ['I was around the club. I would have to check the rack sheet to be sure.', 'Where I always am — my lane and the break room.'],
                'liar' => ['I was at the other end of the club all shift. Somebody can vouch.', 'My lane, the whole shift. Whatever the report says is wrong.'],
            ],
            'unverified' => [
                'honest' => ['I was stocking the shoe room until the evening rush. Check the count log.', 'I was with the crowd at the front desk. Easy to check.'],
                'neutral' => ['I moved around a lot. Half the day I was helping on lanes 1 through 4.', 'I could not say minute by minute. I was busy.'],
                'liar' => ['Ask my coworkers, they will tell you I was nowhere near it.', 'I was here the whole time. That is all you need to know.'],
            ],
        ],
        'log' => [
            'verified' => [
                'honest' => ['The log is right. I thought nobody would check the timestamps.', 'Okay. The timestamp has my initials on it. I am not going to argue with paper.'],
                'neutral' => ['The log says a lot of things. I have made mistakes before.', 'If the log says that, somebody wrote it down wrong.'],
                'liar' => ['Logs can be edited. I would check who was at that computer.', 'That timestamp is not mine. Look closer at the handwriting.'],
            ],
            'unverified' => [
                'honest' => ['Check my sheet — I clocked out at the end of the shift like everyone else.', 'There is no log entry because nothing happened on my shift.'],
                'neutral' => ['I do not keep a log of every step I take.', 'The log would not show anything unusual from me.'],
                'liar' => ['You will not find anything on the log, because this never happened.', 'The log is clean, same as my record.'],
            ],
        ],
        'witness' => [
            'verified' => [
                'honest' => ['If somebody saw it, then they saw it. I will take what is coming.', 'Yeah, somebody was there. I hoped they would not say anything.'],
                'neutral' => ['People see what they want to see.', 'A witness? Who? I want to hear them say it to my face.'],
                'liar' => ['Whoever says they saw me is lying for whoever filed this.', 'A witness to what? There was nothing to witness.'],
            ],
            'unverified' => [
                'honest' => ['Nobody saw anything because I was not doing anything wrong.', 'I doubt any witness backs that story up.'],
                'neutral' => ['There is no witness. They would have come forward already.', 'Witnesses get the story wrong all the time.'],
                'liar' => ['Find me one person who saw me there. You will not.', 'That "witness" is probably the reporter themself.'],
            ],
        ],
        'reporter' => [
            'verified' => [
                'honest' => ['We have history, sure. But the records do not lie, and they line up.', 'I would blame the reporter too. Then I saw the log.'],
                'neutral' => ['We do not exactly share lunch breaks, but that is not the same as lying.', 'Ask yourself why they would risk their job over me.'],
                'liar' => ['They have wanted me gone for months. This is how they do it.', 'That is not a report, that is a grudge.'],
            ],
            'unverified' => [
                'honest' => ['I do not know why they named me. That part does not add up.', 'I have no beef with them, so I am as confused as you are.'],
                'neutral' => ['Maybe they misread something. Maybe they are being used.', 'Somebody fed them a story.'],
                'liar' => ['They are covering for whoever actually did it.', 'Because they are in on it with someone else. That is why.'],
            ],
        ],
    ];

    private const WITNESS_STATEMENTS = [
        'honest' => ['I was not on that lane, so I will not swear either way.', 'They were around the club that shift, same as always.', 'I have no reason to doubt either of them.'],
        'neutral' => ['I only heard about it today, like everyone else.', 'I was busy on my own lanes. I did not watch them.', 'That shift was quiet from where I stood.'],
        'liar' => ['I saw them at the front desk the whole shift.', 'They were nowhere near the lanes. I am sure of it.', 'Honestly, I have heard the reporter twists things.'],
    ];

    private const NARRATIVES = [
        'confessed' => 'After {n} rounds of questioning, {accused} confessed. The story lined up with the report.',
        'bs' => '{accused} brushed the claims off after {n} rounds of questioning, but the records still back the report.',
        'innocent' => 'After {n} rounds of questioning, nothing backed the report. {accused} denies it and the records stay silent.',
    ];

    public function openInterview(Confrontation $confrontation): void
    {
        if ($this->transcript($confrontation)->isNotEmpty()) {
            return;
        }

        $key = $confrontation->db_verified ? 'verified' : 'unverified';
        $tier = $this->tier($confrontation->accused);

        $this->write($confrontation->accused, 'speech', 'interrogation', $this->pick(self::OPENERS[$key][$tier]), $confrontation);
    }

    public function ask(Confrontation $confrontation, string $key, ?string $chipLabel = null): array
    {
        if ($key === 'witness') {
            $reply = $this->witness($confrontation);
        } else {
            $answerKey = $confrontation->db_verified ? 'verified' : 'unverified';
            $tier = $this->tier($confrontation->accused);

            $reply = $this->write(
                $confrontation->accused,
                $this->bubbleFor($key),
                'interrogation',
                $this->pick(self::ANSWERS[$key][$answerKey][$tier] ?? self::ANSWERS[$key]['verified']['neutral']),
                $confrontation
            );
        }

        $userMessage = null;
        if ($chipLabel && ($manager = auth()->user()?->staff)) {
            $userMessage = $this->write(
                $manager,
                'speech',
                'interrogation',
                $chipLabel,
                $confrontation
            );
        }

        return ['reply' => $reply, 'userMessage' => $userMessage];
    }

    public function witness(Confrontation $confrontation): StaffMessage
    {
        $witness = $this->pickWitness($confrontation);

        return $this->write(
            $witness,
            'speech',
            'interrogation',
            $this->pick(self::WITNESS_STATEMENTS[$this->tier($witness)]),
            $confrontation
        );
    }

    public function conclude(Confrontation $confrontation): void
    {
        if ($confrontation->staff_response) {
            return;
        }

        $service = app(ConfrontationService::class);
        $response = $service->rollResponse($confrontation);
        $service->respond($confrontation, $response);

        $n = $this->transcript($confrontation)
            ->filter(fn (StaffMessage $m) => $m->kind === 'interrogation')
            ->count();

        $confrontation->investigation_result = strtr(self::NARRATIVES[$response] ?? self::NARRATIVES['innocent'], [
            '{n}' => max(1, $n),
            '{accused}' => $confrontation->accused->user->name ?? 'The accused',
        ]);
        $confrontation->save();
    }

    public function transcript(Confrontation $confrontation): Collection
    {
        return StaffMessage::with('staff.user')
            ->where('confrontation_id', $confrontation->id)
            ->orderBy('created_at')
            ->get();
    }

    public function chips(): array
    {
        return [
            ['action' => 'ask', 'key' => 'where', 'label' => 'Where were you on that shift?'],
            ['action' => 'ask', 'key' => 'log', 'label' => 'The records place you at the lane.'],
            ['action' => 'ask', 'key' => 'witness', 'label' => 'Anybody see you?'],
            ['action' => 'ask', 'key' => 'reporter', 'label' => 'Why would the reporter name you?'],
            ['action' => 'witness', 'label' => 'Question a coworker.'],
        ];
    }

    public function initials(Staff $staff): string
    {
        $name = trim((string) ($staff->user->name ?? ''));
        if ($name === '') {
            return '??';
        }

        $parts = preg_split('/\s+/', $name);

        return strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
    }

    private function tier(Staff $staff): string
    {
        $score = (int) $staff->honesty_score;
        $names = $staff->personalities->pluck('name')->all();

        if (in_array('honest', $names, true) || $score >= 70) {
            return 'honest';
        }

        if (in_array('opportunistic', $names, true) || in_array('cliquey', $names, true) || $score <= 35) {
            return 'liar';
        }

        return 'neutral';
    }

    private function pickWitness(Confrontation $confrontation): Staff
    {
        $witness = Staff::with('user', 'personalities')
            ->where('is_active', true)
            ->whereNotIn('id', [$confrontation->reporter_staff_id, $confrontation->accused_staff_id])
            ->where('role', '!=', 'club_manager')
            ->inRandomOrder()
            ->first();

        if ($witness) {
            return $witness;
        }

        return $confrontation->reporter;
    }

    private function bubbleFor(string $key): string
    {
        return match ($key) {
            'log' => 'exclamation',
            'witness' => 'thought',
            'reporter' => 'question',
            default => 'speech',
        };
    }

    private function write(Staff $staff, string $bubbleType, string $kind, string $body, Confrontation $confrontation): StaffMessage
    {
        return StaffMessage::create([
            'staff_id' => $staff->id,
            'confrontation_id' => $confrontation->id,
            'bubble_type' => $bubbleType,
            'kind' => $kind,
            'body' => $body,
            'date' => Clock::date(),
        ]);
    }

    private function pick(array $lines): string
    {
        return $lines[array_rand($lines)];
    }
}
