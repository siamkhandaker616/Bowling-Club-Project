<?php

namespace App\Http\Controllers\Sim\Visitor;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Visitor;
use App\Services\Simulation\VisitorRegistry;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $visitor = app(VisitorRegistry::class)->forUser($request->user());

        $complaints = $visitor
            ? Complaint::with('staff.user')->where('visitor_id', $visitor->id)->orderByDesc('created_at')->get()
            : collect();

        return view('sim.visitor.complaints.index', compact('complaints'));
    }

    public function store(Request $request)
    {
        $visitor = app(VisitorRegistry::class)->forUser($request->user());

        if (! $visitor) {
            abort(403, 'No visitor profile linked to this account.');
        }

        $data = $request->validate([
            'type' => ['required', 'in:service,cleanliness,behavior,facility,other'],
            'description' => ['required', 'string', 'max:1000'],
        ]);

        Complaint::create([
            'visitor_id' => $visitor->id,
            'type' => $data['type'],
            'description' => $data['description'],
            'status' => 'open',
        ]);

        session()->flash('success', 'Complaint submitted. A manager will review it.');

        return redirect()->route('visitor.complaints.index');
    }
}
