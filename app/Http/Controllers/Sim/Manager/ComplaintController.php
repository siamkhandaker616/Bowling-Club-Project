<?php

namespace App\Http\Controllers\Sim\Manager;

use App\Helpers\Label;
use App\Http\Controllers\Controller;
use App\Models\Accident;
use App\Models\BookingQueue;
use App\Models\ClubConfig;
use App\Models\Complaint;
use App\Models\LaneBooking;
use App\Services\Simulation\Clock;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index()
    {
        $complaints = Complaint::with('visitor', 'staff.user', 'raisedBy.user')->orderByDesc('created_at')->get();

        return view('sim.manager.complaints.index', compact('complaints'));
    }

    public function resolve(Request $request, Complaint $complaint)
    {
        if ($gate = $this->ensureEscalated($request, $complaint)) {
            return $gate;
        }

        $data = $request->validate([
            'resolution' => ['required', 'string', 'max:1000'],
            'compensation_type' => ['nullable', 'in:free_game,refund,discount,apology,priority_queue'],
        ]);

        $matched = Accident::whereHas('affectedBooking', function ($q) use ($complaint) {
            $q->where('visitor_id', $complaint->visitor_id);
        })->where('resolved', false)->get();

        $matched->each(function (Accident $accident) use ($complaint) {
            $accident->resolved = true;
            $accident->resolution = 'Linked to complaint #'.$complaint->id.' and compensated.';
            $accident->save();
        });

        $complaint->update([
            'status' => 'resolved',
            'resolution' => $data['resolution'].($matched->isNotEmpty() ? ' (Matched '.$matched->count().' accident(s) from the log.)' : ''),
            'compensation_type' => $data['compensation_type'],
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        $this->applyCompensation($complaint, $data['compensation_type'] ?? null);

        $compensationMsg = match ($data['compensation_type'] ?? null) {
            'apology' => ' — apology issued, +2 visitor rep, +1 club rep.',
            'refund' => ' — refund credited ($50 to expenses), +5 visitor rep, +2 club rep.',
            'discount' => ' — discount applied, +3 visitor rep, +1 club rep.',
            'free_game' => ' — free game flagged on next booking, +4 visitor rep, +2 club rep.',
            'priority_queue' => ' — visitor promoted in queue.',
            default => '',
        };

        session()->flash('success', 'Complaint #'.$complaint->id.' resolved'.$compensationMsg.($matched->isNotEmpty() ? ' — matched '.$matched->count().' accident(s).' : '.'));

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'status' => $complaint->status,
                'resolution' => $complaint->resolution,
                'compensation_label' => Label::compensationType($complaint->compensation_type ?? ''),
            ]);
        }

        return redirect()->route('manager.complaints.index');
    }

    private function nextBooking(Complaint $complaint): ?LaneBooking
    {
        return LaneBooking::where('visitor_id', $complaint->visitor_id)
            ->whereIn('status', ['confirmed', 'pending'])
            ->where('date', '>=', Clock::date()->toDateString())
            ->orderBy('date')
            ->orderBy('id')
            ->first();
    }

    private function applyCompensation(Complaint $complaint, ?string $type): void
    {
        $cfg = ClubConfig::singleton();
        $visitor = $complaint->visitor;

        if ($type === 'apology') {
            $cfg->reputation = max(0, min(100, $cfg->reputation + 1));
            $cfg->save();

            if ($visitor) {
                $visitor->reputation_score = max(0, min(100, $visitor->reputation_score + 2));
                $visitor->save();
            }

            return;
        }

        if ($type === 'refund') {
            $cfg->reputation = max(0, min(100, $cfg->reputation + 2));
            $cfg->total_expenses = (float) $cfg->total_expenses + 50;
            $cfg->save();

            if ($visitor) {
                $visitor->reputation_score = max(0, min(100, $visitor->reputation_score + 5));
                $visitor->save();
            }

            $booking = $this->nextBooking($complaint);

            if ($booking) {
                $booking->compensation_claimed = true;
                $booking->compensation_type = 'refund';
                $booking->save();
            }

            return;
        }

        if ($type === 'discount') {
            $cfg->reputation = max(0, min(100, $cfg->reputation + 1));
            $cfg->save();

            if ($visitor) {
                $visitor->reputation_score = max(0, min(100, $visitor->reputation_score + 3));
                $visitor->save();
            }

            $booking = $this->nextBooking($complaint);

            if ($booking) {
                $booking->compensation_claimed = true;
                $booking->compensation_type = 'discount';
                $booking->save();
            }

            return;
        }

        if ($type === 'free_game') {
            $cfg->reputation = max(0, min(100, $cfg->reputation + 2));
            $cfg->save();

            if ($visitor) {
                $visitor->reputation_score = max(0, min(100, $visitor->reputation_score + 4));
                $visitor->save();
            }

            $booking = $this->nextBooking($complaint);

            if ($booking) {
                $booking->compensation_claimed = true;
                $booking->compensation_type = 'free_game';
                $booking->save();
            }

            return;
        }

        if ($type === 'priority_queue') {
            $booking = LaneBooking::where('visitor_id', $complaint->visitor_id)
                ->where('status', 'pending')
                ->where('date', '>=', Clock::date()->toDateString())
                ->orderBy('date')
                ->orderBy('id')
                ->first();

            if (! $booking) {
                return;
            }

            $booking->compensation_type = 'priority_queue';
            $booking->save();

            $entry = BookingQueue::where('booking_id', $booking->id)->first();

            if (! $entry) {
                BookingQueue::create([
                    'booking_id' => $booking->id,
                    'visitor_id' => $booking->visitor_id,
                    'lane_id' => $booking->lane_id,
                    'date' => $booking->date,
                    'time_slot' => $booking->time_slot,
                    'position' => 0,
                    'status' => 'waiting',
                ]);

                $booking->queue_position = 0;
                $booking->save();

                return;
            }

            BookingQueue::whereDate('date', $entry->date)
                ->where('time_slot', $entry->time_slot)
                ->where('status', 'waiting')
                ->where('id', '!=', $entry->id)
                ->increment('position');

            $entry->position = 0;
            $entry->save();

            $booking->queue_position = 0;
            $booking->save();
        }
    }

    public function dismiss(Request $request, Complaint $complaint)
    {
        if ($gate = $this->ensureEscalated($request, $complaint)) {
            return $gate;
        }

        $complaint->update([
            'status' => 'dismissed',
            'resolution' => 'Dismissed by manager.',
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        session()->flash('success', 'Complaint #'.$complaint->id.' dismissed.');

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'status' => $complaint->status,
                'resolution' => $complaint->resolution,
                'compensation_label' => null,
            ]);
        }

        return redirect()->route('manager.complaints.index');
    }

    private function ensureEscalated(Request $request, Complaint $complaint)
    {
        if ($complaint->status === 'investigating') {
            return null;
        }

        $message = $complaint->status === 'open'
            ? 'Blocked — this complaint has not been through the steward yet. It must be escalated at the steward desk before you can act on it.'
            : 'This complaint is already closed ('.$complaint->status.').';

        if ($request->expectsJson()) {
            abort(409, $message);
        }

        session()->flash('error', $message);

        return redirect()->route('manager.complaints.index');
    }
}
