<?php

namespace App\Http\Controllers\Sim\Manager;

use App\Http\Controllers\Controller;
use App\Models\Confrontation;
use App\Models\Staff;
use App\Services\Simulation\ConfrontationService;
use Illuminate\Http\Request;

class ConfrontationController extends Controller
{
    public function __construct(private ConfrontationService $service)
    {
    }

    public function index()
    {
        $confrontations = Confrontation::with('reporter.user', 'accused.user')->orderByDesc('created_at')->get();

        $activeStaff = Staff::with('user')->where('is_active', true)->where('role', '!=', 'club_manager')->get();

        return view('sim.manager.confrontations.index', compact('confrontations', 'activeStaff'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'reporter_staff_id' => ['required', 'exists:staff,id'],
            'accused_staff_id' => ['required', 'exists:staff,id', 'different:reporter_staff_id'],
            'incident_type' => ['required', 'in:theft,sabotage,harassment,negligence,other'],
            'incident_description' => ['nullable', 'string', 'max:1000'],
            'db_verified' => ['nullable', 'boolean'],
        ]);

        $confrontation = $this->service->create(
            $data['reporter_staff_id'],
            $data['accused_staff_id'],
            $data['incident_type'],
            $data['incident_description'] ?? null,
            $request->boolean('db_verified'),
        );

        session()->flash('success', 'Confrontation logged between staff members.');

        return redirect()->route('manager.confrontations.index');
    }

    public function respond(Request $request, Confrontation $confrontation)
    {
        $data = $request->validate([
            'staff_response' => ['required', 'in:confessed,bs,innocent'],
        ]);

        $this->service->respond($confrontation, $data['staff_response']);

        session()->flash('success', 'Accused response recorded.');

        return redirect()->route('manager.confrontations.index');
    }

    public function verdict(Request $request, Confrontation $confrontation)
    {
        $data = $request->validate([
            'verdict' => ['required', 'in:upheld,dismissed,penalized'],
            'penalty_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $this->service->verdict($confrontation, $data['verdict'], $data['penalty_amount'] ?? null);

        session()->flash('success', 'Verdict applied.');

        return redirect()->route('manager.confrontations.index');
    }
}
