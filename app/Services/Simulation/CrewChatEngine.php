<?php

namespace App\Services\Simulation;

use App\Models\Accident;
use App\Models\Confrontation;
use App\Models\Inventory;
use App\Models\Shift;
use App\Models\SnitchReport;
use App\Models\Staff;
use App\Models\StaffEvent;
use App\Models\StaffMessage;
use App\Models\StaffRelationship;
use Illuminate\Support\Collection;

class CrewChatEngine
{
    private const TONES = [
        'hostile' => [
            'speech' => ['I clock in, I do my job, I clock out.', 'Do not talk to me unless it is work.'],
            'thought' => ['Wonder how long this place keeps paying me...', 'The lanes are a mess, as usual.'],
        ],
        'neutral' => [
            'speech' => ['Shifts look normal for this week.', 'Keeping the lanes in decent shape.'],
            'thought' => ['Routine day ahead.', 'Hope we get through the evening rush.'],
        ],
        'friendly' => [
            'speech' => ['Good vibes around the break room lately.', 'The crew is solid, I will say that.', 'Lane 3 runs like a dream after the oil pass.'],
            'thought' => ['Might suggest a crew pizza night.', 'The evening crowd was fun to watch.'],
        ],
        'trusted' => [
            'speech' => ['Between us, management keeps changing the schedule last minute.', 'The bonuses are getting stingier, you notice?'],
            'thought' => ['Everyone knows who carries this place.', 'Payday cannot come soon enough.'],
        ],
    ];

    private const DM_REPLIES = [
        'trusted' => [
            'Between us, the schedule changes are a mess, right?',
            'I would cover your lane if you ever need a break.',
            'You are one of the few I actually trust around here.',
            'Do not repeat this, but I heard bonuses are being cut.',
        ],
        'friendly' => [
            'Yeah, I get that.',
            'We make a decent team, you know?',
            'Catch you in the break room after?',
            'The evening crowd was fun to watch tonight.',
        ],
        'neutral' => [
            'Routine shift, I guess.',
            'We will see how the night goes.',
            'I heard Lane 5 needs attention before the rush.',
            'Sure.',
        ],
        'hostile' => [
            'Do not talk to me unless it is work.',
            'I am busy.',
            'Hmph.',
            'Stay out of my lane.',
        ],
    ];

    private const GROUP_REACTIONS = [
        'Ha, tell me about it.',
        'Wait, really?',
        'Okay, that is fair.',
        'I heard something similar.',
        'Let us just get through today.',
    ];

    public function crewToday(Staff $player): Collection
    {
        $date = Clock::date();

        $shiftStaffIds = Shift::whereDate('date', $date)
            ->where('status', '!=', 'cancelled')
            ->pluck('staff_id')
            ->unique()
            ->all();

        $crew = Staff::with('user', 'personalities')
            ->where('is_active', true)
            ->where('id', '!=', $player->id)
            ->whereIn('id', $shiftStaffIds)
            ->orderBy('id')
            ->get()
            ->filter(fn (Staff $s) => $s->role === 'caretaker');

        if ($crew->count() < 2) {
            $fill = Staff::with('user', 'personalities')
                ->where('is_active', true)
                ->where('id', '!=', $player->id)
                ->where('role', 'caretaker')
                ->orderBy('happiness')
                ->limit(3 - $crew->count())
                ->get();

            $crew = $crew->merge($fill)->unique('id');
        }

        $steward = Staff::with('user', 'personalities')
            ->where('role', 'steward')
            ->where('is_active', true)
            ->first();

        if ($steward) {
            $crew = $crew->push($steward)->unique('id');
        }

        return $crew->values();
    }

    public function ensureSeeded(Staff $player): void
    {
        if (StaffMessage::whereNull('recipient_staff_id')->whereDate('date', Clock::date())->exists()) {
            return;
        }

        $crew = $this->crewToday($player);
        $senders = $crew->filter(fn (Staff $s) => $s->role === 'caretaker')->values();

        $this->write($senders->get(0), 'speech', 'chatter', $this->toneLine($senders->get(0) ?? $player, 'speech'));
        if ($senders->count() > 1) {
            $this->write($senders->get(1), 'thought', 'chatter', $this->toneLine($senders->get(1), 'thought'));
        }

        if ($this->hadAccidentToday($player)) {
            $accuser = $senders->first() ?? $crew->first();

            if ($accuser) {
                $thing = $this->accidentThing($player);

                $this->write(
                    $accuser,
                    'speech',
                    'accusation',
                    $this->groqLine("You left the $thing running again — that's the second time this week.")
                );

                $this->write($player, 'thought', 'thought', 'They are counting. I should smooth this over.');
            }
        }

        $lowOil = (int) (Inventory::where('name', 'Lane Oil')->value('quantity') ?? 0);
        if ($lowOil <= 8) {
            $this->write($senders->get(0) ?? $crew->first(), 'question', 'question', 'We are almost out of lane oil — how do we run a league tonight?');
        }

        $grumpy = $crew->filter(fn (Staff $s) => $s->happiness <= 40)->first();
        if ($grumpy) {
            $this->write($grumpy, 'exclamation', 'warning', 'Not my day... the pinsetter jammed on my shift.');
        } elseif ($steward = $crew->firstWhere('role', 'steward')) {
            $this->write($steward, 'exclamation', 'warning', 'Heard the steward is watching the overtime log. Careful what you sign.');
        }
    }

    public function threadFor(Staff $player): Collection
    {
        $this->ensureSeeded($player);

        return StaffMessage::with('staff.user')
            ->whereNull('recipient_staff_id')
            ->whereDate('date', Clock::date())
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }

    public function messagesSince(int $afterId): Collection
    {
        return StaffMessage::with('staff.user')
            ->whereNull('recipient_staff_id')
            ->where('id', '>', $afterId)
            ->whereDate('date', Clock::date())
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }

    public function dmList(Staff $player): Collection
    {
        return $this->crewToday($player)->map(function (Staff $other) use ($player) {
            $last = StaffMessage::whereDate('date', Clock::date())
                ->where(function ($q) use ($player, $other) {
                    $q->where('staff_id', $player->id)->where('recipient_staff_id', $other->id)
                        ->orWhere('staff_id', $other->id)->where('recipient_staff_id', $player->id);
                })
                ->latest('created_at')
                ->first();

            $unread = StaffMessage::whereDate('date', Clock::date())
                ->where('staff_id', $other->id)
                ->where('recipient_staff_id', $player->id)
                ->whereNull('read_at')
                ->count();

            return [
                'staff' => $other,
                'unread' => $unread,
                'last' => $last?->body,
                'last_by' => $last ? ($last->staff_id === $other->id ? 'them' : 'you') : null,
            ];
        })->values();
    }

    public function dmThread(Staff $player, Staff $other): Collection
    {
        $this->markRead($player, $other);

        return StaffMessage::with('staff.user')
            ->whereDate('date', Clock::date())
            ->where(function ($q) use ($player, $other) {
                $q->where('staff_id', $player->id)->where('recipient_staff_id', $other->id)
                    ->orWhere('staff_id', $other->id)->where('recipient_staff_id', $player->id);
            })
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }

    public function markRead(Staff $player, Staff $other): void
    {
        StaffMessage::whereDate('date', Clock::date())
            ->where('staff_id', $other->id)
            ->where('recipient_staff_id', $player->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function sendMessage(Staff $player, string $body, ?Staff $to = null): array
    {
        $body = trim($body);

        if ($body === '') {
            return ['sent' => false, 'reply' => null];
        }

        $this->write($player, 'speech', 'reply', $body, $to);

        if ($to) {
            return ['sent' => true, 'reply' => $this->dmReply($player, $to, $body)];
        }

        return ['sent' => true, 'reply' => $this->groupReaction($player, $body)];
    }

    public function dmReply(Staff $player, Staff $other, string $playerBody): ?StaffMessage
    {
        if (preg_match('/sorry|apolog|won.t happen|my fault|slipped/i', $playerBody)) {
            $rel = $this->relationship($player, $other);
            $rel->score = max(-100, min(100, $rel->score + 8));
            $rel->level = $this->levelFor($rel->score);
            $rel->save();

            return $this->write($other, 'speech', 'chat', $this->pick([
                'Apology accepted. Do not let it happen again.',
                'It is okay. I was more annoyed than mad.',
                'Fine. I am keeping an eye on you, though.',
            ]), $player);
        }

        if (preg_match('/\?|who|what|when|where|why|how/i', $playerBody)) {
            return $this->write($other, 'speech', 'chat', $this->pick([
                'Honestly? I am still figuring that out myself.',
                'Not sure, but I would ask the steward before the shift.',
                'Good question. I will check the log later.',
            ]), $player);
        }

        $tone = $this->levelFor($this->relationship($player, $other)->score);

        if (($other->happiness <= 50 || $tone === 'hostile') && mt_rand(1, 100) <= 30) {
            return $this->write($other, 'speech', 'chat', $this->pick([
                'Between us, payroll has been short the last two weeks and management will not explain.',
                'Do not say it loudly, but I saw the steward trimming the overtime log.',
                'Keep this between us — they are watching who clocks out early.',
            ]), $player);
        }

        $bank = self::DM_REPLIES[$tone] ?? self::DM_REPLIES['neutral'];

        return $this->write($other, 'speech', 'chat', $this->pick($bank), $player);
    }

    public function groupReaction(Staff $player, string $body): ?StaffMessage
    {
        $crew = $this->crewToday($player)->filter(fn (Staff $s) => $s->role === 'caretaker' && $s->id !== $player->id)->values();

        if ($crew->isEmpty() || mt_rand(1, 100) > 55) {
            return null;
        }

        return $this->write($crew->random(), 'speech', 'chatter', $this->pick(self::GROUP_REACTIONS));
    }

    public function vibeChips(Staff $player): array
    {
        $recent = StaffMessage::whereNull('recipient_staff_id')
            ->whereDate('date', Clock::date())
            ->where('staff_id', '!=', $player->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        if ($recent->contains('kind', 'accusation')) {
            return [
                ['label' => 'I am sorry — it will not happen again.', 'action' => 'send'],
                ['label' => 'That is not what happened.', 'action' => 'send'],
                ['label' => 'Let us talk after the shift.', 'action' => 'send'],
            ];
        }

        $text = $recent->pluck('body')->implode(' ');

        if (preg_match('/steward|overtime|payroll|watching|log/i', $text)) {
            return [
                ['label' => 'Thanks for the heads up.', 'action' => 'send'],
                ['label' => 'Noted. Staying careful.', 'action' => 'send'],
                ['label' => 'Who told you that?', 'action' => 'send'],
            ];
        }

        if (preg_match('/oil|stock|restock|running low|out of/i', $text)) {
            return [
                ['label' => 'Let us check the stock room.', 'action' => 'send'],
                ['label' => 'We can manage until the order lands.', 'action' => 'send'],
                ['label' => 'Put a restock request in.', 'action' => 'send'],
            ];
        }

        return match ($this->crewVibe($player)) {
            'friendly', 'trusted' => [
                ['label' => 'Ha, true.', 'action' => 'send'],
                ['label' => 'Same honestly.', 'action' => 'send'],
                ['label' => 'Sounds like a plan.', 'action' => 'send'],
            ],
            'hostile' => [
                ['label' => 'Easy there.', 'action' => 'send'],
                ['label' => 'Let us keep it professional.', 'action' => 'send'],
                ['label' => 'Can we talk about this later?', 'action' => 'send'],
            ],
            default => [
                ['label' => 'Yeah, busy day.', 'action' => 'send'],
                ['label' => 'Tell me about it.', 'action' => 'send'],
                ['label' => 'We will manage.', 'action' => 'send'],
            ],
        };
    }

    public function dmChips(Staff $player, Staff $other, ?StaffMessage $lastIncoming = null): array
    {
        $chips = [];

        if ($lastIncoming && $this->isDamning($lastIncoming->body)) {
            $chips[] = ['label' => 'Snitch 🐦', 'action' => 'snitch', 'message_id' => $lastIncoming->id];
        }

        $tone = $this->levelFor($this->relationship($player, $other)->score);

        $rest = match ($tone) {
            'friendly', 'trusted' => [
                ['label' => 'I hear you.', 'action' => 'send'],
                ['label' => 'Tell me more.', 'action' => 'send'],
                ['label' => 'Haha, exactly.', 'action' => 'send'],
            ],
            'hostile' => [
                ['label' => 'Okay, okay.', 'action' => 'send'],
                ['label' => 'Right.', 'action' => 'send'],
                ['label' => 'Whatever you say.', 'action' => 'send'],
            ],
            default => [
                ['label' => 'Got it.', 'action' => 'send'],
                ['label' => 'Noted.', 'action' => 'send'],
                ['label' => 'I will see you at the shift.', 'action' => 'send'],
            ],
        };

        return array_slice(array_merge($chips, $rest), 0, 3);
    }

    public function isDamning(string $body): bool
    {
        return (bool) preg_match('/payroll|overtime log|trimming|watching who|short|sting/i', $body);
    }

    public function applyReply(Staff $player, StaffMessage $message, string $action): array
    {
        if ((int) $message->staff_id === (int) $player->id) {
            return ['flash' => 'That was your own message.', 'type' => 'error'];
        }

        $sender = $message->staff;

        return match ($action) {
            'apologize' => $this->apologize($player, $sender),
            'stay_quiet' => $this->stayQuiet($player, $sender),
            'snitch' => $this->snitch($player, $sender, $message->body),
            default => ['flash' => 'That is not a move you can make here.', 'type' => 'error'],
        };
    }

    public function vent(Staff $player): array
    {
        $log = [];
        app(SocialEngine::class)->vent($player, Clock::date(), $log);

        $line = $log['trash_talk'][0]['line'] ?? 'Management really does not see the work we put in.';
        $this->write($player, 'speech', 'vent', $line);

        $heardBy = $this->crewToday($player)
            ->filter(fn (Staff $s) => $s->id !== $player->id)
            ->pluck('user.name')
            ->map(fn ($n) => $n ?? 'Somebody')
            ->values()
            ->all();

        $snitches = $log['snitches'] ?? [];

        return [
            'heard_by' => $heardBy,
            'snitched' => count($snitches) > 0,
            'snitch_name' => $snitches[0]['snitch'] ?? null,
        ];
    }

    public function respondToConfrontation(Staff $player, Confrontation $confrontation, string $response): void
    {
        app(ConfrontationService::class)->respond($confrontation, $response);

        $this->write($player, 'thought', 'reply', match ($response) {
            'confessed' => 'I slipped up. It will not happen again.',
            'innocent' => 'Not me. I had nothing to do with it.',
            default => 'This is a joke. Somebody is fishing.',
        });

        StaffEvent::create([
            'staff_id' => $player->id,
            'event_type' => 'confrontation_response',
            'severity' => 'moderate',
            'description' => "Responded to a confrontation as $response.",
            'date' => Clock::date(),
            'happiness_change' => 0,
        ]);
    }

    public function relationshipsFor(Staff $player): Collection
    {
        return $this->crewToday($player)->map(function (Staff $other) use ($player) {
            $rel = $this->relationship($player, $other);

            return [
                'staff' => $other,
                'score' => $rel->score,
                'level' => $this->levelFor($rel->score),
            ];
        })->values();
    }

    public function snitchLedger(Staff $player): Collection
    {
        return SnitchReport::with('accused.user', 'confrontation')
            ->where('reporter_staff_id', $player->id)
            ->orderByDesc('created_at')
            ->get();
    }

    public function relationship(Staff $a, Staff $b): StaffRelationship
    {
        $aId = (int) $a->id;
        $bId = (int) $b->id;

        $rel = StaffRelationship::where(function ($q) use ($aId, $bId) {
            $q->where('staff_a_id', $aId)->where('staff_b_id', $bId);
        })->orWhere(function ($q) use ($aId, $bId) {
            $q->where('staff_a_id', $bId)->where('staff_b_id', $aId);
        })->first();

        if ($rel) {
            return $rel;
        }

        return StaffRelationship::create([
            'staff_a_id' => min($aId, $bId),
            'staff_b_id' => max($aId, $bId),
            'level' => 'neutral',
            'score' => 0,
        ]);
    }

    public function levelFor(int $score): string
    {
        return match (true) {
            $score >= 25 => 'trusted',
            $score >= 8 => 'friendly',
            $score > -8 => 'neutral',
            default => 'hostile',
        };
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

    private function crewVibe(Staff $player): string
    {
        $scores = $this->crewToday($player)->map(function (Staff $other) use ($player) {
            return $this->relationship($player, $other)->score;
        });

        return $scores->isEmpty() ? 'neutral' : $this->levelFor((int) round($scores->avg()));
    }

    private function apologize(Staff $player, Staff $sender): array
    {
        $rel = $this->relationship($player, $sender);
        $rel->score = max(-100, min(100, $rel->score + 8));
        $rel->level = $this->levelFor($rel->score);
        $rel->save();

        $sender->happiness = max(0, min(100, $sender->happiness + 3));
        $sender->save();

        StaffEvent::create([
            'staff_id' => $sender->id,
            'event_type' => 'social',
            'severity' => null,
            'description' => 'You apologized in the break room — trust nudged up.',
            'date' => Clock::date(),
            'happiness_change' => 3,
        ]);

        $this->write($player, 'speech', 'reply', 'Sorry about that. I will watch it next time.');

        return ['flash' => 'Apologized to ' . ($sender->user->name ?? 'your coworker') . ' — trust nudged up.', 'type' => 'success'];
    }

    private function stayQuiet(Staff $player, Staff $sender): array
    {
        $rel = $this->relationship($player, $sender);
        $rel->score = max(-100, min(100, $rel->score + 1));
        $rel->level = $this->levelFor($rel->score);
        $rel->save();

        $this->write($player, 'thought', 'reply', '...kept my mouth shut.');

        return ['flash' => 'Stayed quiet — no change.', 'type' => 'success'];
    }

    private function snitch(Staff $player, Staff $target, ?string $quote): array
    {
        $report = SnitchReport::create([
            'reporter_staff_id' => $player->id,
            'accused_staff_id' => $target->id,
            'quote' => $quote,
            'status' => 'pending',
        ]);

        $rel = $this->relationship($player, $target);
        $rel->score = max(-100, min(100, $rel->score - 10));
        $rel->level = $this->levelFor($rel->score);
        $rel->save();

        foreach ($this->crewToday($player) as $witness) {
            if ($witness->id === $player->id || $witness->id === $target->id) {
                continue;
            }
            $wrel = $this->relationship($player, $witness);
            $wrel->score = max(-100, min(100, $wrel->score - 4));
            $wrel->level = $this->levelFor($wrel->score);
            $wrel->save();
        }

        StaffEvent::create([
            'staff_id' => $player->id,
            'event_type' => 'snitch_report',
            'severity' => null,
            'description' => 'Filed a snitch report on ' . ($target->user->name ?? 'a coworker') . ' to the steward.',
            'date' => Clock::date(),
            'happiness_change' => 0,
        ]);

        $this->write($player, 'thought', 'reply', 'Someone should know about that.');

        return [
            'flash' => 'Snitch report #' . $report->id . ' sent to the steward. The crew remembers...',
            'type' => 'success',
        ];
    }

    private function write(?Staff $staff, string $bubbleType, string $kind, string $body, ?Staff $to = null): ?StaffMessage
    {
        if (! $staff || trim($body) === '') {
            return null;
        }

        return StaffMessage::create([
            'staff_id' => $staff->id,
            'recipient_staff_id' => $to?->id,
            'bubble_type' => $bubbleType,
            'kind' => $kind,
            'body' => $body,
            'date' => Clock::date(),
        ]);
    }

    private function hadAccidentToday(Staff $staff): bool
    {
        return Accident::where('staff_id', $staff->id)
            ->whereDate('created_at', Clock::date())
            ->exists();
    }

    private function accidentThing(Staff $staff): string
    {
        $accident = Accident::where('staff_id', $staff->id)
            ->whereDate('created_at', Clock::date())
            ->first();

        return match (true) {
            $accident && stripos((string) $accident->type, 'oil') !== false => 'oil machine',
            $accident && stripos((string) $accident->type, 'pin') !== false => 'pinsetter',
            $accident && stripos((string) $accident->type, 'shoe') !== false => 'shoe rack',
            default => 'lane equipment',
        };
    }

    private function toneLine(Staff $member, string $type): string
    {
        $rels = StaffRelationship::where('staff_a_id', $member->id)
            ->orWhere('staff_b_id', $member->id)
            ->get();

        $tone = $rels->isEmpty() ? 'neutral' : $this->levelFor((int) round($rels->avg('score')));
        $bank = self::TONES[$tone][$type] ?? self::TONES['neutral'][$type];

        return $this->pick($bank);
    }

    private function pick(array $lines): string
    {
        return $lines[array_rand($lines)];
    }

    private function groqLine(string $fallback): string
    {
        if (! config('services.groq.enabled', false)) {
            return $fallback;
        }

        return $fallback;
    }
}
