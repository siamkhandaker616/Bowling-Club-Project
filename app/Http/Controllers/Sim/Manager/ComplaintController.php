<?php

namespace App\Http\Controllers\Sim\Manager;

use App\Http\Controllers\Controller;
use App\Models\Accident;
use App\Models\BookingQueue;
use App\Models\ClubConfig;
use App\Models\Complaint;
use App\Models\LaneBooking;
use App\Models\Visitor;
use App\Models\VisitorReview;
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
        $data = $request->validate([
            'resolution' => ['required', 'string', 'max:1000'],
            'compensation_type' => ['nullable', 'in:free_game,refund,discount,apology,priority_queue'],
        ]);

        $matched = Accident::whereHas('affectedBooking', function ($q) use ($complaint) {
            $q->where('visitor_id', $complaint->visitor_id);
        })->where('resolved', false)->get();

        $matched->each(function (Accident $accident) use ($complaint) {
            $accident->resolved = true;
            $accident->resolution = 'Linked to complaint #' . $complaint->id . ' and compensated.';
            $accident->save();
        });

        $complaint->update([
            'status' => 'resolved',
            'resolution' => $data['resolution'] . ($matched->isNotEmpty() ? ' (Matched ' . $matched->count() . ' accident(s) from the log.)' : ''),
            'compensation_type' => $data['compensation_type'],
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        $this->applyCompensation($complaint, $data['compensation_type'] ?? null);

        session()->flash('success', 'Complaint #' . $complaint->id . ' resolved' . ($matched->isNotEmpty() ? ' — matched ' . $matched->count() . ' accident(s) in the log.' : '') . '.');

        return redirect()->route('manager.complaints.index');
    }

    private function applyCompensation(Complaint $complaint, ?string $type): void
    {
        if ($type === 'free_game') {
            $booking = LaneBooking::where('visitor_id', $complaint->visitor_id)
                ->whereIn('status', ['confirmed', 'pending'])
                ->where('date', '>=', Clock::date()->toDateString())
                ->orderBy('date')
                ->orderBy('id')
                ->first();

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
        $complaint->update([
            'status' => 'dismissed',
            'resolution' => 'Dismissed by manager.',
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        session()->flash('success', 'Complaint #' . $complaint->id . ' dismissed.');

        return redirect()->route('manager.complaints.index');
    }
}
