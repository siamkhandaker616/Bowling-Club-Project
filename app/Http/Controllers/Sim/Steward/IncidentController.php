<?php

namespace App\Http\Controllers\Sim\Steward;

use App\Helpers\Label;
use App\Http\Controllers\Controller;
use App\Models\Confrontation;
use App\Models\Staff;
use App\Services\Simulation\ConfrontationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IncidentController extends Controller
{
    public function index()
    {
        $confrontations = Confrontation::with('reporter.user', 'accused.user')
            ->orderByDesc('created_at')
            ->get();

        $reporters = Staff::with('user')
            ->where('is_active', true)
            ->where('role', '!=', 'club_manager')
            ->orderBy('id')
            ->get();

        $accused = Staff::with('user')
            ->where('is_active', true)
            ->where('role', 'caretaker')
            ->orderBy('id')
            ->get();

        return view('sim.steward.incidents.index', compact('confrontations', 'reporters', 'accused'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateIncident($request);

        $confrontation = app(ConfrontationService::class)->create(
            (int) $data['reporter_staff_id'],
            (int) $data['accused_staff_id'],
            $data['incident_type'],
            $data['incident_description'] ?? null,
            $request->boolean('db_verified'),
        );

        return response()->json([
            'ok' => true,
            'id' => $confrontation->id,
            'date' => $confrontation->date,
            'incident_label' => Label::incidentType($confrontation->incident_type),
            'reporter' => $confrontation->reporter->user->name ?? 'Crew',
            'accused' => $confrontation->accused->user->name ?? 'Crew',
            'description' => $confrontation->incident_description,
            'db_verified' => (bool) $confrontation->db_verified,
        ], 201);
    }

    private function validateIncident(Request $request): array
    {
        $data = $request->validate([
            'reporter_staff_id' => ['required', 'integer', 'exists:staff,id'],
            'accused_staff_id' => ['required', 'integer', 'exists:staff,id', 'different:reporter_staff_id'],
            'incident_type' => ['required', 'in:theft,sabotage,harassment,negligence,other'],
            'incident_description' => ['nullable', 'string', 'max:1000'],
            'db_verified' => ['nullable', 'boolean'],
        ]);

        $accused = Staff::find($data['accused_staff_id']);

        if (! $accused || $accused->role !== 'caretaker') {
            abort(422, 'Only caretakers can be accused — confrontations with any other role are not allowed.');
        }

        if ($accused && ! $accused->is_active) {
            abort(422, 'That caretaker is no longer on the active crew.');
        }

        return $data;
    }
}
