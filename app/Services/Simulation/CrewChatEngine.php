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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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
        'No way. That is wild.',
        'Yeah, things have been crazy around here.',
        'Tell me more about that.',
        'I was thinking the same thing.',
        'About time someone said it.',
        'Right? I was just saying that.',
        'That tracks, honestly.',
        'Hmm, interesting.',
        'Not surprised, to be honest.',
        'Same energy, honestly.',
    ];

    private const PERSONALITY_BANKS = [
        'question' => [
            'base' => [
                'I honestly do not know.',
                'Good question — no idea though.',
                'Beats me. I just clock in and do the work.',
                'That is above my pay grade.',
                'I wish I had an answer for you.',
                'Hmm, I would not know where to start.',
                'You would have to dig into that yourself.',
                'I have been wondering the same thing, honestly.',
                'No clue. Try asking someone who has been here longer.',
                'I stay out of that kind of stuff.',
            ],
            'honest' => [
                'I will be straight — I have no clue.',
                'I do not know, but I am not going to pretend I do.',
                'Honestly? I have been asking myself the same thing.',
            ],
            'stoner' => [
                'Dunno, man. That is way above my pay grade.',
                'Who knows? I am just here for the vibes.',
                'Not a clue, but it is all good.',
            ],
            'overtly_friendly' => [
                'Oh gosh, I wish I knew! That sounds important!',
                'I am not sure, but I can ask around for you!',
                'Hmm, I do not know — but let me find out!',
            ],
            'creepy' => [
                'I have my theories... but you did not hear that from me.',
                'Why do you want to know? ...Just curious.',
                'I know more than you think. But that is a story for later.',
            ],
            'nerd' => [
                'That is actually a fascinating question — I would check the shift log.',
                'Statistically speaking, the answer is probably in the records.',
                'I have read about something similar — let me think.',
            ],
            'rude' => [
                'How should I know? I just work here.',
                'Not my problem. Ask someone who cares.',
                'Seriously? You are asking me?',
            ],
            'cliquey' => [
                'Not my problem. Talk to someone else.',
                'I stay out of that stuff. You should too.',
                'I do not know, and honestly I do not want to.',
            ],
            'opportunistic' => [
                'I might know something... but information is not free.',
                'I have heard some things. What is it worth to you?',
                'Maybe. Why, what have you heard?',
            ],
        ],
        'oil' => [
            'base' => [
                'We need to get that sorted before the rush.',
                'I heard the stockroom is running thin on that.',
                'Put a request in — management needs to know.',
                'We cannot run lanes without supplies.',
                'Yeah, that has been a problem for a while now.',
                'Someone needs to handle that before it gets worse.',
                'The last order came in short, I think.',
                'Check with the manager — they should know.',
            ],
            'honest' => [
                'I will be honest — we are running low and nobody is doing anything about it.',
                'The stock situation is not great. I would put a request in.',
            ],
            'stoner' => [
                'Dunno, man. I just use whatever is there.',
                'The stockroom is looking pretty empty, not gonna lie.',
            ],
            'overtly_friendly' => [
                'Oh no, is it running low? I can help check!',
                'We should totally get that restocked — I will help!',
            ],
            'creepy' => [
                'I know where the extra stock is hidden... interesting.',
                'The stockroom is running dry. Do not tell anyone I said that.',
            ],
            'nerd' => [
                'According to my tracking, we are at 30% capacity on oil.',
                'The inventory numbers are not looking good.',
            ],
            'rude' => [
                'Not my job to restock. Figure it out.',
                'That is management\'s problem, not mine.',
            ],
            'cliquey' => [
                'I am not touching that. Someone else can deal with it.',
                'The stock situation is someone else\'s mess.',
            ],
            'opportunistic' => [
                'I could handle the restock... for the right incentive.',
                'Stock is low. Good leverage if you need it.',
            ],
        ],
        'schedule' => [
            'base' => [
                'The schedule has been all over the place lately.',
                'I would check the board before signing up for extra.',
                'They keep changing it last minute, it is frustrating.',
                'Shift swaps are fine just talk to the steward first.',
                'I heard they are rotating the evening slots next week.',
                'The morning crew always gets the better shifts.',
                'I would not volunteer for overtime unless you need the money.',
                'Check the board — they updated it this morning.',
            ],
            'honest' => [
                'I will be straight — the schedule is a mess right now.',
                'They changed it again yesterday. I would double check.',
            ],
            'stoner' => [
                'Dunno, man. I just show up when they tell me.',
                'The schedule is whatever. I just roll with it.',
            ],
            'overtly_friendly' => [
                'Oh, the schedule? I can help you figure it out!',
                'Let me know if you need a swap — I am flexible!',
            ],
            'creepy' => [
                'I know who is pulling the strings on the schedule... but that is a secret.',
                'The schedule changes are not random. There is a pattern.',
            ],
            'nerd' => [
                'The scheduling algorithm seems suboptimal, honestly.',
                'I have been tracking the shift patterns — there is a trend.',
            ],
            'rude' => [
                'The schedule sucks. Always has, always will.',
                'Not my problem. I work what they give me.',
            ],
            'cliquey' => [
                'I got my shifts sorted. You should too.',
                'Not my problem. Talk to the steward.',
            ],
            'opportunistic' => [
                'I could swap with you... for the right favor.',
                'The schedule is flexible if you know the right people.',
            ],
        ],
        'apologize' => [
            'base' => [
                'It happens. Just try to be more careful.',
                'We all make mistakes. Learn from it.',
                'That is the second time this week though.',
                'Appreciate you owning up to it.',
                'Just do not let it become a habit.',
                'Thanks for saying something.',
            ],
            'honest' => [
                'I appreciate the honesty. That takes guts.',
                'We all mess up. What matters is you owned it.',
            ],
            'stoner' => [
                'No worries, man. It happens.',
                'All good. Just chill and be more careful.',
            ],
            'overtly_friendly' => [
                'Aww, do not worry about it! We all slip up!',
                'It is okay! Just be more careful next time!',
            ],
            'creepy' => [
                'Interesting... you are very forthcoming about your mistakes.',
                'I will remember that you admitted to that.',
            ],
            'nerd' => [
                'Statistically, accidents happen most in the first hour.',
                'I would review the safety protocols if I were you.',
            ],
            'rude' => [
                'Yeah, yeah. Just fix it.',
                'About time you said something.',
            ],
            'cliquey' => [
                'Whatever. Just do not mess up my shift.',
                'I do not care. Just fix it.',
            ],
            'opportunistic' => [
                'Noted. I might need a favor from you later.',
                'Interesting. Good to know you are the type to own up.',
            ],
        ],
        'pinsetter' => [
            'base' => [
                'Lane maintenance is behind schedule as it is.',
                'I heard the pinsetter on lane 5 has been acting up.',
                'We need to stay on top of that before tonight.',
                'The oil machine needs a proper look, not a quick patch.',
                'That has been an issue all week.',
                'Someone should log a ticket for that.',
            ],
            'honest' => [
                'I will be straight — that lane needs attention before the rush.',
                'The maintenance log is backed up. We need to prioritize.',
            ],
            'stoner' => [
                'Dunno, man. The machines are vibes right now.',
                'The pinsetter is being weird. Just roll with it.',
            ],
            'overtly_friendly' => [
                'Oh no, is the lane acting up? I can take a look!',
                'We should get that fixed — I will help!',
            ],
            'creepy' => [
                'I know a thing or two about machines... if you ask nicely.',
                'The pinsetter has been making noises. Interesting.',
            ],
            'nerd' => [
                'The pinsetter\'s cycle timing is off by about 200ms.',
                'I ran diagnostics — the oil distribution is uneven.',
            ],
            'rude' => [
                'Not my lane, not my problem.',
                'Figure it out. I am busy.',
            ],
            'cliquey' => [
                'I am not touching that machine. Someone else can.',
                'That is not my section. Ask someone else.',
            ],
            'opportunistic' => [
                'I could fix it... for the right consideration.',
                'Maintenance work is above my pay grade. Unless there is a bonus.',
            ],
        ],
        'steward' => [
            'base' => [
                'Careful what you say — walls have ears around here.',
                'Management does not exactly keep us in the loop.',
                'I would keep that between us.',
                'They are watching the overtime logs closely.',
                'I heard they are cutting costs again.',
                'Best not to rock the boat on that one.',
            ],
            'honest' => [
                'I will be honest — I do not trust management right now.',
                'They are not being straight with us. I would keep your head down.',
            ],
            'stoner' => [
                'Dunno, man. I do not pay attention to that stuff.',
                'Management is whatever. I just do my thing.',
            ],
            'overtly_friendly' => [
                'Oh gosh, do not say that too loud!',
                'I am sure they mean well... right?',
            ],
            'creepy' => [
                'I hear things. More than you think.',
                'Management is hiding something. I can feel it.',
            ],
            'nerd' => [
                'The organizational hierarchy is not exactly transparent.',
                'I have been documenting the policy changes. It is not pretty.',
            ],
            'rude' => [
                'Management does not care about us. Period.',
                'They are all the same. Do not expect anything.',
            ],
            'cliquey' => [
                'I stay out of management\'s way. You should too.',
                'Not my problem. I just work here.',
            ],
            'opportunistic' => [
                'I have some connections in management. Might be useful.',
                'Information about management is worth its weight in gold.',
            ],
        ],
        'tired' => [
            'base' => [
                'The break room fridge has some leftover pizza if you want.',
                'Hang in there — shift is almost over.',
                'I hear you. These double shifts are rough.',
                'Take five when you can. Nobody will notice.',
                'The break room coffee is fresh, if that helps.',
                'I am right there with you. Long day.',
            ],
            'honest' => [
                'I will be honest — I am wiped too. This week has been brutal.',
                'We all need a break. Take one when you can.',
            ],
            'stoner' => [
                'Dunno, man. The break room has some snacks though.',
                'I hear you. Just ride it out.',
            ],
            'overtly_friendly' => [
                'Oh no, are you tired? I can cover for a bit!',
                'Let me grab you some coffee! Hang in there!',
            ],
            'creepy' => [
                'I know a quiet spot where nobody will find you... for a break.',
                'Tired? ...I know the feeling.',
            ],
            'nerd' => [
                'According to labor studies, a 5-minute break increases productivity by 15%.',
                'I would take a break if I were you. The data supports it.',
            ],
            'rude' => [
                'Yeah, yeah. We are all tired. Get over it.',
                'Take a break or do not. I do not care.',
            ],
            'cliquey' => [
                'I am taking my break. You are on your own.',
                'Not my problem. Figure it out.',
            ],
            'opportunistic' => [
                'I could cover for you... for a favor.',
                'Take a break. I will remember this.',
            ],
        ],
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
                    $this->groqLine(
                        "You left the $thing running again — that's the second time this week.",
                        $accuser->user->name ?? 'A coworker',
                        "You left the $thing running again",
                        $accuser,
                        0.6
                    )
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
            return ['sent' => false, 'reply' => null, 'replies' => collect()];
        }

        $this->write($player, 'speech', 'reply', $body, $to);

        if ($to) {
            $reply = $this->dmReply($player, $to, $body);

            return ['sent' => true, 'reply' => $reply, 'replies' => $reply ? collect([$reply]) : collect()];
        }

        $replies = $this->groupReaction($player, $body);

        return ['sent' => true, 'reply' => $replies->first(), 'replies' => $replies];
    }

    public function dmReply(Staff $player, Staff $other, string $playerBody): ?StaffMessage
    {
        if (preg_match('/sorry|apolog|won.t happen|my fault|slipped/i', $playerBody)) {
            $rel = $this->relationship($player, $other);
            $rel->score = max(-100, min(100, $rel->score + 8));
            $rel->level = $this->levelFor($rel->score);
            $rel->save();

            return $this->write($other, 'speech', 'chat', $this->groqLine(
                $this->pick([
                    'Apology accepted. Do not let it happen again.',
                    'It is okay. I was more annoyed than mad.',
                    'Fine. I am keeping an eye on you, though.',
                ]),
                $other->user->name ?? 'Coworker',
                $playerBody,
                $other,
                1.0
            ), $player);
        }

        if (preg_match('/\?|who|what|when|where|why|how/i', $playerBody)) {
            return $this->write($other, 'speech', 'chat', $this->groqLine(
                $this->pick([
                    'Honestly? I am still figuring that out myself.',
                    'Not sure, but I would ask the steward before the shift.',
                    'Good question. I will check the log later.',
                ]),
                $other->user->name ?? 'Coworker',
                $playerBody,
                $other,
                1.0
            ), $player);
        }

        $tone = $this->levelFor($this->relationship($player, $other)->score);

        if (($other->happiness <= 50 || $tone === 'hostile') && mt_rand(1, 100) <= 30) {
            return $this->write($other, 'speech', 'chat', $this->groqLine(
                $this->pick([
                    'Between us, payroll has been short the last two weeks and management will not explain.',
                    'Do not say it loudly, but I saw the steward trimming the overtime log.',
                    'Keep this between us — they are watching who clocks out early.',
                ]),
                $other->user->name ?? 'Coworker',
                $playerBody,
                $other,
                1.0
            ), $player);
        }

        $bank = self::DM_REPLIES[$tone] ?? self::DM_REPLIES['neutral'];

        return $this->write($other, 'speech', 'chat', $this->groqLine(
            $this->pick($bank),
            $other->user->name ?? 'Coworker',
            $playerBody,
            $other,
            1.0
        ), $player);
    }

    /** @return Collection<int, StaffMessage> */
    public function groupReaction(Staff $player, string $body): Collection
    {
        $crew = $this->crewToday($player)->filter(fn (Staff $s) => $s->role === 'caretaker' && $s->id !== $player->id)->values();

        $replies = collect();
        $used = [];

        foreach ($crew as $npc) {
            if (mt_rand(1, 100) <= 55) {
                $fallback = $this->pick(self::GROUP_REACTIONS, $used);
                $line = $this->groqLine(
                    $fallback,
                    $player->user->name ?? 'a coworker',
                    $body,
                    $npc,
                    0.6,
                    $used
                );
                $used[] = $line;
                $msg = $this->write($npc, 'speech', 'chatter', $line);
                if ($msg) {
                    $replies->push($msg);
                }
            }
        }

        return $replies;
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

        $last = $recent->first();
        $text = strtolower($last?->body ?? '');
        $mood = $this->crewVibe($player);

        if (preg_match('/oil|stock|restock|running low|out of/i', $text)) {
            return match ($mood) {
                'friendly', 'trusted' => [
                    ['label' => 'I will check the stock room.', 'action' => 'send'],
                    ['label' => 'We can manage until the order lands.', 'action' => 'send'],
                    ['label' => 'I will put a restock request in.', 'action' => 'send'],
                ],
                default => [
                    ['label' => 'Somebody needs to order more.', 'action' => 'send'],
                    ['label' => 'That is not my department.', 'action' => 'send'],
                    ['label' => 'Tell the manager.', 'action' => 'send'],
                ],
            };
        }

        if (preg_match('/schedule|shift|roster|cover|overtime/i', $text)) {
            return match ($mood) {
                'friendly', 'trusted' => [
                    ['label' => 'I can swap if you need.', 'action' => 'send'],
                    ['label' => 'Check the board — they might have updated it.', 'action' => 'send'],
                    ['label' => 'I heard they are rotating evening slots.', 'action' => 'send'],
                ],
                default => [
                    ['label' => 'The schedule has been rough lately.', 'action' => 'send'],
                    ['label' => 'Talk to the steward about swaps.', 'action' => 'send'],
                    ['label' => 'I would not volunteer for extra unless you need it.', 'action' => 'send'],
                ],
            };
        }

        if (preg_match('/pinsetter|jam|lane\s*\d|maintenance|oil machine/i', $text)) {
            return match ($mood) {
                'friendly', 'trusted' => [
                    ['label' => 'I can take a look at it.', 'action' => 'send'],
                    ['label' => 'We should log a ticket before the rush.', 'action' => 'send'],
                    ['label' => 'Has anyone reported it yet?', 'action' => 'send'],
                ],
                default => [
                    ['label' => 'Lane maintenance is behind again.', 'action' => 'send'],
                    ['label' => 'That has been an issue all week.', 'action' => 'send'],
                    ['label' => 'Someone needs to handle that.', 'action' => 'send'],
                ],
            };
        }

        if (preg_match('/tired|break|food|hungry|exhausted|long day/i', $text)) {
            return match ($mood) {
                'friendly', 'trusted' => [
                    ['label' => 'Hang in there — shift is almost over.', 'action' => 'send'],
                    ['label' => 'The break room has leftover pizza.', 'action' => 'send'],
                    ['label' => 'Take five when you can.', 'action' => 'send'],
                ],
                default => [
                    ['label' => 'I hear you. These double shifts are rough.', 'action' => 'send'],
                    ['label' => 'The break room coffee is fresh.', 'action' => 'send'],
                    ['label' => 'We are all tired. Hang in there.', 'action' => 'send'],
                ],
            };
        }

        if (preg_match('/steward|manager|boss|management|payroll|log|overtime/i', $text)) {
            return match ($mood) {
                'friendly', 'trusted' => [
                    ['label' => 'Careful what you say around here.', 'action' => 'send'],
                    ['label' => 'I heard the same thing.', 'action' => 'send'],
                    ['label' => 'Walls have ears, you know.', 'action' => 'send'],
                ],
                default => [
                    ['label' => 'They do not exactly keep us in the loop.', 'action' => 'send'],
                    ['label' => 'I would keep that between us.', 'action' => 'send'],
                    ['label' => 'Best not to rock the boat.', 'action' => 'send'],
                ],
            };
        }

        if (preg_match('/\?|who|what|when|where|why|how/i', $text)) {
            return match ($mood) {
                'friendly', 'trusted' => [
                    ['label' => 'I was wondering the same thing.', 'action' => 'send'],
                    ['label' => 'Good question — let me know what you find out.', 'action' => 'send'],
                    ['label' => 'I have been asking myself that.', 'action' => 'send'],
                ],
                default => [
                    ['label' => 'No clue. I just work here.', 'action' => 'send'],
                    ['label' => 'Beats me.', 'action' => 'send'],
                    ['label' => 'You would have to ask someone else.', 'action' => 'send'],
                ],
            };
        }

        return match ($mood) {
            'friendly', 'trusted' => [
                ['label' => 'Ha, true.', 'action' => 'send'],
                ['label' => 'Same honestly.', 'action' => 'send'],
                ['label' => 'Tell me about it.', 'action' => 'send'],
            ],
            'hostile' => [
                ['label' => 'Easy there.', 'action' => 'send'],
                ['label' => 'Let us keep it professional.', 'action' => 'send'],
                ['label' => 'Can we talk about this later?', 'action' => 'send'],
            ],
            default => [
                ['label' => 'Yeah, busy day.', 'action' => 'send'],
                ['label' => 'Right?', 'action' => 'send'],
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

        if ($lastIncoming) {
            $body = strtolower($lastIncoming->body);

            if (preg_match('/oil|stock|restock|supplies|running low/i', $body)) {
                $chips[] = ['label' => 'I will check the stock room.', 'action' => 'send'];
                $chips[] = ['label' => 'We should put a request in.', 'action' => 'send'];
                $chips[] = ['label' => 'That has been a problem for a while.', 'action' => 'send'];
            } elseif (preg_match('/schedule|shift|roster|cover|overtime/i', $body)) {
                $chips[] = ['label' => 'I can swap if you need.', 'action' => 'send'];
                $chips[] = ['label' => 'The schedule has been rough.', 'action' => 'send'];
                $chips[] = ['label' => 'Check the board — they updated it.', 'action' => 'send'];
            } elseif (preg_match('/accident|mistake|broke|jammed|my fault/i', $body)) {
                $chips[] = ['label' => 'It happens to everyone.', 'action' => 'send'];
                $chips[] = ['label' => 'Just be more careful next time.', 'action' => 'send'];
                $chips[] = ['label' => 'We all mess up sometimes.', 'action' => 'send'];
            } elseif (preg_match('/tired|break|food|long day|exhausted/i', $body)) {
                $chips[] = ['label' => 'Hang in there — shift is almost over.', 'action' => 'send'];
                $chips[] = ['label' => 'The break room has food.', 'action' => 'send'];
                $chips[] = ['label' => 'Take five when you can.', 'action' => 'send'];
            } elseif (preg_match('/steward|manager|boss|management|payroll/i', $body)) {
                $chips[] = ['label' => 'Careful what you say around here.', 'action' => 'send'];
                $chips[] = ['label' => 'I heard the same thing.', 'action' => 'send'];
                $chips[] = ['label' => 'Walls have ears, you know.', 'action' => 'send'];
            } elseif (preg_match('/\?|who|what|when|where|why|how/i', $body)) {
                $chips[] = ['label' => 'I was wondering the same thing.', 'action' => 'send'];
                $chips[] = ['label' => 'No clue. I just work here.', 'action' => 'send'];
                $chips[] = ['label' => 'You would have to ask someone else.', 'action' => 'send'];
            } elseif (preg_match('/complaint|frustrated|annoyed|upset|angry/i', $body)) {
                $chips[] = ['label' => 'I get how you feel.', 'action' => 'send'];
                $chips[] = ['label' => 'Maybe talk to the manager about it.', 'action' => 'send'];
                $chips[] = ['label' => 'That sounds rough.', 'action' => 'send'];
            } elseif (preg_match('/pinsetter|lane|maintenance|clean|oil/i', $body)) {
                $chips[] = ['label' => 'Lane maintenance is behind again.', 'action' => 'send'];
                $chips[] = ['label' => 'Has anyone logged a ticket?', 'action' => 'send'];
                $chips[] = ['label' => 'That has been an issue all week.', 'action' => 'send'];
            }
        }

        $rest = match ($tone) {
            'friendly', 'trusted' => [
                ['label' => 'I hear you.', 'action' => 'send'],
                ['label' => 'Tell me more.', 'action' => 'send'],
                ['label' => 'Same honestly.', 'action' => 'send'],
            ],
            'hostile' => [
                ['label' => 'Okay, okay.', 'action' => 'send'],
                ['label' => 'Right.', 'action' => 'send'],
                ['label' => 'Whatever you say.', 'action' => 'send'],
            ],
            default => [
                ['label' => 'Got it.', 'action' => 'send'],
                ['label' => 'Noted.', 'action' => 'send'],
                ['label' => 'Right?', 'action' => 'send'],
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

    private function pick(array $lines, ?array $used = null): string
    {
        if ($used !== null) {
            $available = array_values(array_diff($lines, $used));
            if ($available !== []) {
                $lines = $available;
            }
        }

        return $lines[array_rand($lines)];
    }

    private function personalityBank(string $topic, ?Staff $speaker, ?array $used = null): string
    {
        $bank = self::PERSONALITY_BANKS[$topic] ?? [];
        $base = $bank['base'] ?? [];

        $personality = $speaker?->personalities?->first()?->name ?? null;
        $extras = $bank[$personality] ?? [];

        return $this->pick(array_merge($base, $extras), $used);
    }

    private function groqLine(string $fallback, string $speakerName, string $playerMessage, ?Staff $speaker = null, float $probability = 0.6, ?array $used = null): string
    {
        if (! config('services.groq.enabled', false)) {
            return $this->ruleBasedFallback($fallback, $playerMessage, $used, $speaker);
        }

        if (mt_rand(1, 100) > ($probability * 100)) {
            return $this->ruleBasedFallback($fallback, $playerMessage, $used, $speaker);
        }

        $tone = 'neutral';
        $personality = 'standard';
        if ($speaker) {
            $tone = $this->crewVibe($speaker);
            $personality = $speaker->personalities->pluck('name')->implode(', ') ?: 'standard';
        }

        $systemPrompt = "You are {$speakerName}, a bowling alley " . ($speaker->role ?? 'staff member') . ". "
            . "Your personality: {$personality}. Your mood: {$tone}. "
            . "You speak in short, casual sentences that match your personality. "
            . "Never break character. Never mention being an AI. "
            . "Stay in 1-2 sentences max. Respond naturally to what was said.";

        $result = $this->groqChat($systemPrompt, $playerMessage);

        return $result ?? $this->ruleBasedFallback($fallback, $playerMessage, $used, $speaker);
    }

    private function ruleBasedFallback(string $fallback, string $playerMessage, ?array $used = null, ?Staff $speaker = null): string
    {
        $lower = strtolower($playerMessage);

        if (preg_match('/oil|stock|restock|supplies|running low/i', $lower)) {
            return $this->personalityBank('oil', $speaker, $used);
        }

        if (preg_match('/schedule|shift|roster|cover|overtime/i', $lower)) {
            return $this->personalityBank('schedule', $speaker, $used);
        }

        if (preg_match('/sorry|apolog|my fault|won.t happen|slipped|accident/i', $lower)) {
            return $this->personalityBank('apologize', $speaker, $used);
        }

        if (preg_match('/pinsetter|jam|lane\s*\d|maintenance|oil machine/i', $lower)) {
            return $this->personalityBank('pinsetter', $speaker, $used);
        }

        if (preg_match('/steward|manager|boss|management|payroll|log/i', $lower)) {
            return $this->personalityBank('steward', $speaker, $used);
        }

        if (preg_match('/tired|break|food|hungry|exhausted|long day/i', $lower)) {
            return $this->personalityBank('tired', $speaker, $used);
        }

        if (preg_match('/\?|who|what|when|where|why|how/i', $lower)) {
            return $this->personalityBank('question', $speaker, $used);
        }

        return $fallback;
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
                'temperature' => 0.8,
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
