<?php

namespace App\Http\Controllers\Sim\Manager;

use App\Http\Controllers\Controller;
use App\Models\Confrontation;
use App\Models\Staff;
use App\Models\StaffMessage;
use App\Services\Simulation\ConfrontationService;
use App\Services\Simulation\InterrogationEngine;
use Illuminate\Http\JsonResponse;
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
        if ($confrontation->staff_response) {
            return redirect()->route('manager.confrontations.index');
        }

        $this->service->autoRespond($confrontation);
        session()->flash('success', 'Investigation complete — the accused was weighed against the records.');

        return redirect()->route('manager.confrontations.index');
    }

    public function interview(Confrontation $confrontation): JsonResponse
    {
        if ($confrontation->staff_response) {
            abort(409, 'This confrontation has already been answered.');
        }

        $engine = app(InterrogationEngine::class);
        $engine->openInterview($confrontation);

        $accused = $confrontation->accused;

        return response()->json([
            'messages' => $engine->transcript($confrontation)->map(fn (StaffMessage $m) => $this->payload($m))->values(),
            'chips' => $engine->chips(),
            'accused' => [
                'id' => $accused->id,
                'name' => $accused->user->name ?? 'The accused',
                'initials' => $engine->initials($accused),
            ],
        ]);
    }

    public function interrogate(Request $request, Confrontation $confrontation): JsonResponse
    {
        if ($confrontation->staff_response) {
            abort(409, 'This confrontation has already been answered.');
        }

        $key = (string) $request->validate(['key' => ['required', 'in:where,log,witness,reporter']])['key'];

        $engine = app(InterrogationEngine::class);

        $chips = $engine->chips();
        $chipLabel = collect($chips)->first(fn ($c) => ($c['key'] ?? $c['action'] ?? null) === $key)['label'] ?? null;

        $result = $engine->ask($confrontation, $key, $chipLabel);

        return response()->json([
            'userMessage' => $result['userMessage'] ? $this->payload($result['userMessage']) : null,
            'reply' => $this->payload($result['reply']),
            'chips' => $chips,
        ]);
    }

    public function conclude(Confrontation $confrontation)
    {
        if ($confrontation->staff_response) {
            return redirect()->route('manager.confrontations.index');
        }

        app(InterrogationEngine::class)->conclude($confrontation);
        session()->flash('success', 'Investigation concluded.');

        return redirect()->route('manager.confrontations.index');
    }

    public function verdict(Request $request, Confrontation $confrontation)
    {
        $data = $request->validate([
            'verdict' => ['required', 'in:upheld,dismissed,penalized,reporter_penalized'],
            'penalty_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $this->service->verdict($confrontation, $data['verdict'], $data['penalty_amount'] ?? null);

        session()->flash('success', 'Verdict applied.');

        return redirect()->route('manager.confrontations.index');
    }

    private function payload(StaffMessage $message): array
    {
        $staff = $message->staff;

        return [
            'id' => $message->id,
            'name' => $staff->user->name ?? 'Crew',
            'initials' => app(InterrogationEngine::class)->initials($staff),
            'bubble_type' => $message->bubble_type,
            'body' => $message->body,
        ];
    }
}
