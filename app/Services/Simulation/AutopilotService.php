<?php

namespace App\Services\Simulation;

use App\Models\Accident;
use App\Models\BanRequest;
use App\Models\Bonus;
use App\Models\ClubConfig;
use App\Models\Complaint;
use App\Models\Lane;
use App\Models\LaneBooking;
use App\Models\Shift;
use App\Models\SnitchReport;
use App\Models\Staff;
use App\Models\StaffEvent;
use App\Models\TouringRequest;
use App\Models\VisitorReview;
use Illuminate\Support\Facades\Cache;

class AutopilotService
{
    private const CACHE_KEY = 'sim:autopilot:beat';

    /** Items younger than this are left for humans. */
    private const AGE_MINUTES = 30;

    /** Per-run caps so the NPCs act with judgement, not in batches. */
    private const CAPS = [
        'complaints' => 3,
        'snitches' => 2,
        'bans' => 1,
        'touring' => 1,
        'shifts' => 2,
        'lanes' => 2,
        'reviews' => 2,
    ];

    public function __construct(private DayCycle $dayCycle) {}

    /**
     * Runs at most once every 5 minutes (throttled) and only touches items
     * older than AGE_MINUTES, so fresh fixtures and human-paced play are
     * never stepped on. Returns a summary of autonomous actions taken.
     */
    public function run(): array
    {
        if (Cache::has(self::CACHE_KEY)) {
            return [];
        }

        Cache::put(self::CACHE_KEY, now(), 300);

        return [
            'escalated_complaints' => $this->escalateComplaints(),
            'snitch_triage' => $this->triageSnitchReports(),
            'ban_decisions' => $this->decideBanRequests(),
            'touring_decisions' => $this->decideTouringRequests(),
            'shifts_completed' => $this->caretakerDuties(),
            'reviews_posted' => $this->visitorReviews(),
        ];
    }

    // ------------------------------------------------------------------
    // Steward domain — complaint triage
    // ------------------------------------------------------------------

    private function escalateComplaints(): int
    {
        $cutoff = now()->subMinutes(self::AGE_MINUTES);

        $aged = Complaint::with('visitor')
            ->where('status', 'open')
            ->where('created_at', '<', $cutoff)
            ->orderBy('created_at')
            ->get();

        $escalated = 0;

        foreach ($aged as $complaint) {
            if ($escalated >= self::CAPS['complaints']) {
                break;
            }

            $accidentLinked = Accident::where('resolved', false)
                ->whereHas('affectedBooking', fn ($q) => $q->where('visitor_id', $complaint->visitor_id))
                ->exists();

            if (! $accidentLinked) {
                $chance = ($complaint->visitor?->tier ?? 'regular') === 'premium' ? 0.55 : 0.35;
                if (! $this->roll($chance)) {
                    continue;
                }
            }

            $note = $accidentLinked
                ? 'Auto-escalated by the steward — visitor booking matches an accident on the floor log.'
                : 'Auto-escalated by the steward after triage of the overnight desk.';

            $complaint->update([
                'status' => 'investigating',
                'resolution' => $note,
            ]);

            $escalated++;
        }

        return $escalated;
    }

    private function triageSnitchReports(): int
    {
        $cutoff = now()->subMinutes(self::AGE_MINUTES);

        $pending = SnitchReport::with('reporter.personalities', 'accused')
            ->where('status', 'pending')
            ->where('created_at', '<', $cutoff)
            ->orderBy('created_at')
            ->limit(6)
            ->get();

        $handled = 0;

        foreach ($pending as $report) {
            if ($handled >= self::CAPS['snitches']) {
                break;
            }

            $score = 0.5
                + ((int) ($report->reporter->honesty_score ?? 50) - 50) / 200
                - ((int) ($report->accused->honesty_score ?? 50) - 50) / 300;

            $score = max(0.15, min(0.85, $score));

            if ($this->roll($score)) {
                $description = trim('Overheard: "'.($report->quote ?? 'a coworker trash-talking management').'" Auto-escalated by the steward after reviewing the report.');

                $confrontation = app(ConfrontationService::class)->create(
                    $report->reporter_staff_id,
                    $report->accused_staff_id,
                    'other',
                    $description,
                    true,
                );

                $report->update([
                    'status' => 'escalated',
                    'confrontation_id' => $confrontation->id,
                    'steward_note' => 'Steward autopilot: credible report, escalated.',
                    'escalated_at' => now(),
                ]);

                $reporter = $report->reporter;
                if ($reporter) {
                    $reporter->happiness = max(0, min(100, $reporter->happiness + 5));
                    $reporter->save();

                    Bonus::create([
                        'staff_id' => $reporter->id,
                        'type' => 'recognition',
                        'reason' => 'Snitch report validated by the steward',
                        'amount_or_hours' => 0,
                        'date' => Clock::date(),
                        'issued_by' => null,
                    ]);

                    StaffEvent::create([
                        'staff_id' => $reporter->id,
                        'event_type' => 'bonus',
                        'severity' => 'positive',
                        'description' => 'Snitch report auto-escalated to the manager',
                        'date' => Clock::date(),
                        'happiness_change' => 5,
                    ]);
                }
            } else {
                $report->update([
                    'status' => 'dismissed',
                    'steward_note' => 'Steward autopilot: not enough substance, dismissed.',
                    'resolved_at' => now(),
                ]);
            }

            $handled++;
        }

        return $handled;
    }

    // ------------------------------------------------------------------
    // Manager-adjacent paperwork — bans & touring (never confrontations)
    // ------------------------------------------------------------------

    private function decideBanRequests(): int
    {
        $cutoff = now()->subMinutes(self::AGE_MINUTES);

        $pending = BanRequest::with('visitor')
            ->where('status', 'pending')
            ->where('created_at', '<', $cutoff)
            ->orderBy('created_at')
            ->limit(self::CAPS['bans'])
            ->get();

        $decided = 0;

        foreach ($pending as $request) {
            $rep = (int) ($request->visitor->reputation_score ?? 50);
            $approveChance = max(0.15, min(0.85,
                0.4
                + ($request->evidence ? 0.25 : 0)
                + (60 - $rep) / 200
            ));

            if ($this->roll($approveChance)) {
                $request->update([
                    'status' => 'approved',
                    'reviewed_by_admin_id' => null,
                    'reviewed_at' => now(),
                    'admin_notes' => 'Autopilot review: record supports the request.',
                ]);

                $request->visitor->update([
                    'is_banned' => true,
                    'ban_reason' => $request->reason,
                    'banned_by_admin_id' => null,
                    'banned_at' => now(),
                ]);
            } else {
                $request->update([
                    'status' => 'denied',
                    'reviewed_by_admin_id' => null,
                    'reviewed_at' => now(),
                    'admin_notes' => 'Autopilot review: insufficient grounds.',
                ]);
            }

            $decided++;
        }

        return $decided;
    }

    private function decideTouringRequests(): int
    {
        $cfg = ClubConfig::singleton();
        $cutoff = now()->subMinutes(self::AGE_MINUTES);

        $pending = TouringRequest::where('status', 'pending')
            ->where('created_at', '<', $cutoff)
            ->orderBy('arrival_date')
            ->limit(self::CAPS['touring'])
            ->get();

        $decided = 0;

        foreach ($pending as $request) {
            $saneSize = $request->player_count >= 2 && $request->player_count <= 10;
            $confirmChance = $cfg->reputation >= 55 ? ($saneSize ? 0.8 : 0.35) : ($saneSize ? 0.4 : 0.2);

            if ($this->roll($confirmChance)) {
                $request->update(['status' => 'confirmed']);
            } else {
                $request->update(['status' => 'declined']);
            }

            $decided++;
        }

        return $decided;
    }

    // ------------------------------------------------------------------
    // Caretaker NPC semi-autonomy — shifts & lane oil
    // ------------------------------------------------------------------

    private function caretakerDuties(): int
    {
        $today = Clock::date();

        $npcCaretakerIds = Staff::where('is_active', true)
            ->where('role', 'caretaker')
            ->whereHas('user', fn ($q) => $q->where('is_npc', true))
            ->pluck('id');

        $completed = 0;

        Shift::with('staff')
            ->whereDate('date', $today)
            ->where('status', 'scheduled')
            ->whereIn('staff_id', $npcCaretakerIds)
            ->orderBy('date')
            ->get()
            ->each(function (Shift $shift) use (&$completed) {
                if ($completed >= self::CAPS['shifts']) {
                    return;
                }

                $mood = (int) ($shift->staff?->happiness ?? 50);
                $chance = $mood <= 20 ? 0.25 : 0.8;

                if ($this->roll($chance)) {
                    $this->dayCycle->markShiftComplete($shift);
                    $completed++;
                }
            });

        if ($npcCaretakerIds->isNotEmpty()) {
            Lane::where('oil_level', '<', 20)
                ->where('status', 'open')
                ->orderBy('oil_level')
                ->limit(self::CAPS['lanes'])
                ->get()
                ->each(function (Lane $lane) {
                    if ($this->roll(0.7)) {
                        $lane->update([
                            'oil_level' => 100,
                            'last_maintained_at' => now(),
                        ]);
                    }
                });
        }

        return $completed;
    }

    // ------------------------------------------------------------------
    // Visitor NPC semi-autonomy — reviews after completed bookings
    // ------------------------------------------------------------------

    private function visitorReviews(): int
    {
        $yesterday = Clock::date()->copy()->subDay();

        $bookings = LaneBooking::with('visitor')
            ->whereDate('date', '<=', $yesterday->toDateString())
            ->where('status', 'completed')
            ->whereHas('visitor', fn ($q) => $q->where('is_banned', false))
            ->whereDoesntHave('visitorReview')
            ->orderByDesc('date')
            ->limit(10)
            ->get();

        $posted = 0;

        foreach ($bookings as $booking) {
            if ($posted >= self::CAPS['reviews']) {
                break;
            }

            $rep = (int) (ClubConfig::singleton()->reputation);

            $base = (int) round($rep / 20); // reputation 100 → 5 stars
            $rating = max(1, min(5, $base + rand(-1, 1)));

            if ($this->roll(0.6)) {
                VisitorReview::create([
                    'visitor_id' => $booking->visitor_id,
                    'booking_id' => $booking->id,
                    'rating' => $rating,
                    'body' => $this->reviewLine($rating),
                ]);

                $posted++;
            }
        }

        return $posted;
    }

    private function reviewLine(int $rating): string
    {
        return match (true) {
            $rating >= 5 => 'Best lanes in town — slick oil, sharp pins, zero complaints.',
            $rating >= 4 => 'Solid night out. Lanes ran clean all session.',
            $rating >= 3 => 'Decent vibes, nothing special. Would come back.',
            $rating >= 2 => 'Place was a bit rough around the edges tonight.',
            default => 'Not my best visit. The lanes need attention.',
        };
    }

    private function roll(float $chance): bool
    {
        return mt_rand(1, 100) / 100 <= $chance;
    }
}
