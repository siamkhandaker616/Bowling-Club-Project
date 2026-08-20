<?php

namespace App\Services\Simulation;

use App\Models\Confrontation;
use App\Models\Staff;
use App\Models\StaffMessage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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
            $transcript = $this->transcript($confrontation)->pluck('body')->implode(' | ');
            $tier = $this->tier($confrontation->accused);
            $replyBody = $this->groqInterrogationLine($confrontation, $key, $transcript, $tier);

            $reply = $this->write(
                $confrontation->accused,
                $this->bubbleFor($key),
                'interrogation',
                $replyBody,
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

    /** @return array<int, array{action: string, key?: string, label: string}> */
    public function chips(Confrontation $confrontation): array
    {
        $transcript = $this->transcript($confrontation);
        $msgCount = $transcript->filter(fn (StaffMessage $m) => $m->kind === 'interrogation' && $m->staff_id !== auth()->user()?->staff?->id)->count();

        $lastAccused = $transcript->where('staff_id', $confrontation->accused_staff_id)->last();
        $lastBody = strtolower($lastAccused?->body ?? '');

        $chips = [];

        if ($msgCount >= 3) {
            $chips[] = ['action' => 'conclude', 'label' => 'Conclude investigation'];
        }

        if ($msgCount === 0) {
            $chips[] = ['action' => 'ask', 'key' => 'where', 'label' => 'Where were you on that shift?'];
            $chips[] = ['action' => 'ask', 'key' => 'log', 'label' => 'The records place you at the lane.'];
            $chips[] = ['action' => 'ask', 'key' => 'reporter', 'label' => 'Why would the reporter name you?'];
            $chips[] = ['action' => 'witness', 'label' => 'Question a coworker.'];

            return $chips;
        }

        if (preg_match('/deny|nothing|not me|wasn.t|no idea|joke|wrong/i', $lastBody)) {
            $chips[] = ['action' => 'ask', 'key' => 'where', 'label' => 'Where exactly were you?'];
            $chips[] = ['action' => 'ask', 'key' => 'log', 'label' => 'The records say otherwise.'];
            $chips[] = ['action' => 'ask', 'key' => 'reporter', 'label' => 'Why would they name you specifically?'];
        } elseif (preg_match('/lane|shift|room|desk|front|back|break/i', $lastBody)) {
            $chips[] = ['action' => 'ask', 'key' => 'log', 'label' => 'The timestamps do not match your story.'];
            $chips[] = ['action' => 'ask', 'key' => 'witness', 'label' => 'Let us hear what a coworker saw.'];
            $chips[] = ['action' => 'ask', 'key' => 'reporter', 'label' => 'What is your relationship with the reporter?'];
        } elseif (preg_match('/log|record|timestamp|paper|document/i', $lastBody)) {
            $chips[] = ['action' => 'ask', 'key' => 'where', 'label' => 'So where does the record go wrong?'];
            $chips[] = ['action' => 'ask', 'key' => 'witness', 'label' => 'A coworker might clarify this.'];
            $chips[] = ['action' => 'ask', 'key' => 'reporter', 'label' => 'Do you think the reporter forged it?'];
        } elseif (preg_match('/witness|saw|saw me|vouch|anyone/i', $lastBody)) {
            $chips[] = ['action' => 'ask', 'key' => 'where', 'label' => 'Let us go back — where were you?'];
            $chips[] = ['action' => 'ask', 'key' => 'log', 'label' => 'I have the log right here. Explain this.'];
            $chips[] = ['action' => 'ask', 'key' => 'reporter', 'label' => 'Is this personal between you two?'];
        } elseif (preg_match('/reporter|grudge|personal|target|cover/i', $lastBody)) {
            $chips[] = ['action' => 'ask', 'key' => 'where', 'label' => 'Regardless — where were you?'];
            $chips[] = ['action' => 'ask', 'key' => 'log', 'label' => 'The records still place you there.'];
            $chips[] = ['action' => 'witness', 'label' => 'Let me ask someone else.'];
        } else {
            $chips[] = ['action' => 'ask', 'key' => 'where', 'label' => 'Tell me exactly where you were.'];
            $chips[] = ['action' => 'ask', 'key' => 'log', 'label' => 'What does the log show?'];
            $chips[] = ['action' => 'ask', 'key' => 'witness', 'label' => 'Can anyone back that up?'];
            $chips[] = ['action' => 'ask', 'key' => 'reporter', 'label' => 'Why do you think you were reported?'];
        }

        if ($msgCount >= 2 && ! in_array('witness', array_column($chips, 'key'), true)) {
            $chips[] = ['action' => 'witness', 'label' => 'Question a coworker.'];
        }

        return array_slice($chips, 0, 5);
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

    private function groqInterrogationLine(Confrontation $confrontation, string $key, string $transcript, string $tier): string
    {
        $answerKey = $confrontation->db_verified ? 'verified' : 'unverified';
        $fallback = $this->pick(self::ANSWERS[$key][$answerKey][$tier] ?? self::ANSWERS[$key]['verified']['neutral']);

        if (! config('services.groq.enabled', false)) {
            return $fallback;
        }

        if (mt_rand(1, 100) > 80) {
            return $fallback;
        }

        $accused = $confrontation->accused;
        $personality = $accused->personalities->pluck('name')->implode(', ') ?: 'ordinary';
        $role = $accused->role;

        $systemPrompt = "You are being interrogated at a bowling alley. You are a {$role} with a {$personality} personality. "
            . "Your honesty level is: {$tier}. "
            . ($tier === 'liar' ? "You are guilty and will deflect, deny, and make excuses. Never confess fully." : '')
            . ($tier === 'honest' ? "You are mostly honest. If the evidence is strong, you bend toward the truth." : '')
            . ($tier === 'neutral' ? "You are evasive. You give partial answers and avoid committing." : '')
            . " Keep responses to 1-2 sentences. Stay in character. Never break the fourth wall.";

        $questionContext = match ($key) {
            'where' => 'The manager asked: Where exactly were you on that shift?',
            'log' => 'The manager confronted you with log/timestamp evidence.',
            'reporter' => 'The manager asked why the reporter named you.',
            default => 'The manager is questioning you about an incident.',
        };

        $result = $this->groqChat($systemPrompt, $questionContext . ($transcript !== '' ? "\n\nSo far in the conversation: " . Str::limit($transcript, 300) : ''));

        return $result ?? $fallback;
    }

    private function groqChat(string $systemPrompt, string $userMessage): ?string
    {
        try {
            $apiKey = config('services.groq.api_key');

            if (empty($apiKey)) {
                return null;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(5)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'groq/compound-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'max_tokens' => 80,
                'temperature' => 0.85,
            ]);

            if ($response->successful()) {
                $body = $response->json('choices.0.message.content', '');
                $body = trim($body);

                if ($body !== '' && Str::length($body) <= 200) {
                    return $body;
                }
            }
        } catch (\Throwable) {
            // Silently fall back
        }

        return null;
    }
}
