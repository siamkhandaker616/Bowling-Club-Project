<?php

namespace App\Http\Controllers\Sim\Steward;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Visitor;
use App\Services\Simulation\Clock;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index()
    {
        $complaints = Complaint::with('visitor', 'staff.user')->orderByDesc('created_at')->get();

        $visitors = Visitor::orderBy('name')->get();

        return view('sim.steward.complaints.index', compact('complaints', 'visitors'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'visitor_id' => ['required', 'exists:visitors,id'],
            'type' => ['required', 'in:service,cleanliness,behavior,facility,other'],
            'description' => ['required', 'string', 'max:1000'],
        ]);

        Complaint::create([
            'visitor_id' => $data['visitor_id'],
            'raised_by_staff_id' => $request->user()->staff->id,
            'type' => $data['type'],
            'description' => $data['description'],
            'status' => 'open',
        ]);

        session()->flash('success', 'Complaint logged.');

        return redirect()->route('steward.complaints.index');
    }

    public function escalate(Request $request, Complaint $complaint)
    {
        $complaint->update([
            'status' => 'investigating',
            'resolution' => 'Escalated to manager by steward.',
        ]);

        session()->flash('success', 'Complaint escalated.');

        return redirect()->route('steward.complaints.index');
    }
}
