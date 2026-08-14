<?php

namespace App\Http\Controllers\Sim\Manager;

use App\Http\Controllers\Controller;
use App\Models\Accident;
use App\Models\ClubConfig;
use App\Models\Complaint;
use App\Models\Visitor;
use App\Models\VisitorReview;
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

        session()->flash('success', 'Complaint #' . $complaint->id . ' resolved' . ($matched->isNotEmpty() ? ' — matched ' . $matched->count() . ' accident(s) in the log.' : '') . '.');

        return redirect()->route('manager.complaints.index');
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
