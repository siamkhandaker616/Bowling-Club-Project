<?php

namespace App\Http\Controllers\Sim\Caretaker;

use App\Http\Controllers\Controller;
use App\Models\ClubConfig;
use App\Models\Confrontation;
use App\Models\Staff;
use App\Models\StaffMessage;
use App\Services\Simulation\Clock;
use App\Services\Simulation\CrewChatEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CrewController extends Controller
{
    public function index(Request $request)
    {
        $me = $request->user()->staff;

        if (! $me || ! $me->is_active) {
            abort(403);
        }

        $engine = app(CrewChatEngine::class);

        $today = Clock::date();
        $day = ClubConfig::singleton()->current_day;

        $thread = $engine->threadFor($me);
        $dms = $engine->dmList($me);

        $open = null;
        $dmThread = collect();
        $dmChips = [];

        $with = (int) $request->query('with', 0);
        if ($with && $with !== $me->id) {
            $other = Staff::with('user')
                ->where('is_active', true)
                ->whereIn('role', ['caretaker', 'steward'])
                ->find($with);

            if ($other) {
                $open = $other;
                $dmThread = $engine->dmThread($me, $other);
                $dmChips = $engine->dmChips($me, $other, $dmThread->filter(fn (StaffMessage $m) => (int) $m->staff_id === (int) $other->id)->last());
            }
        }

        $vibe = $engine->vibeChips($me);
        $crewRelations = $engine->relationshipsFor($me);
        $ledger = $engine->snitchLedger($me);
        $accusations = Confrontation::with('reporter.user', 'accused.user')
            ->where('accused_staff_id', $me->id)
            ->whereNull('manager_verdict')
            ->orderByDesc('created_at')
            ->get();

        $confrontations = Confrontation::with('reporter.user', 'accused.user')
            ->where('reporter_staff_id', $me->id)
            ->orWhere('accused_staff_id', $me->id)
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        $tabs = ['crew', 'dm', 'reported', 'relationships', 'ledger', 'history'];
        $tab = $request->query('tab', 'crew');
        if (! in_array($tab, $tabs, true)) {
            $tab = 'crew';
        }

        return view('sim.caretaker.crew.index', compact('me', 'thread', 'dms', 'open', 'dmThread', 'dmChips', 'vibe', 'crewRelations', 'ledger', 'accusations', 'confrontations', 'engine', 'day', 'today', 'tab'));
    }

    public function poll(Request $request): JsonResponse
    {
        $me = $request->user()->staff;

        $engine = app(CrewChatEngine::class);

        $messages = $engine
            ->messagesSince((int) $request->query('after', 0))
            ->map(fn (StaffMessage $message) => $this->payload($message, $me));

        return response()->json([
            'messages' => $messages->values(),
            'chips' => $engine->vibeChips($me),
            'typing' => $engine->typingFor($me, 'group'),
        ]);
    }

    public function dm(Request $request): JsonResponse
    {
        $me = $request->user()->staff;

        $other = Staff::with('user')
            ->where('is_active', true)
            ->whereIn('role', ['caretaker', 'steward'])
            ->find((int) $request->query('with', 0));

        if (! $other || (int) $other->id === (int) $me->id) {
            abort(404);
        }

        $engine = app(CrewChatEngine::class);

        $thread = $engine->dmThread($me, $other);
        $lastIncoming = $thread->filter(fn (StaffMessage $m) => (int) $m->staff_id === (int) $other->id)->last();

        return response()->json([
            'messages' => $thread->map(fn (StaffMessage $m) => $this->payload($m, $me))->values(),
            'chips' => $engine->dmChips($me, $other, $lastIncoming),
            'other' => [
                'id' => $other->id,
                'name' => $other->user->name ?? 'Crew',
                'initials' => $engine->initials($other),
            ],
            'typing' => $engine->typingFor($me, 'dm', $other),
        ]);
    }

    public function typing(Request $request): JsonResponse
    {
        $me = $request->user()->staff;

        $context = $request->query('context', 'group');
        $withId = $request->integer('with', 0);
        $other = $withId ? Staff::find($withId) : null;

        $typing = app(CrewChatEngine::class)->typingFor($me, $context, $other);

        return response()->json(['typing' => $typing]);
    }

    public function send(Request $request): JsonResponse
    {
        $me = $request->user()->staff;

        $data = $request->validate([
            'body' => ['required', 'string', 'max:500'],
            'to' => ['nullable', 'integer', 'exists:staff,id'],
        ]);

        $to = $data['to'] ?? null;

        if ($to && (int) $to === (int) $me->id) {
            abort(403);
        }

        $result = app(CrewChatEngine::class)->sendMessage($me, $data['body'], $to ? Staff::find($to) : null);

        return response()->json(['ok' => $result['sent']]);
    }

    public function reply(Request $request, StaffMessage $message): RedirectResponse
    {
        $me = $request->user()->staff;

        if ($message->date->format('Y-m-d') !== Clock::date()->format('Y-m-d')) {
            session()->flash('error', 'That message has gone stale.');

            return redirect()->route('caretaker.crew.index');
        }

        $action = (string) $request->validate(['action' => ['required', 'in:apologize,stay_quiet,snitch']])['action'];

        $result = app(CrewChatEngine::class)->applyReply($me, $message, $action);

        session()->flash($result['type'] === 'error' ? 'error' : 'success', $result['flash']);

        $with = $request->integer('with');

        return $with
            ? redirect()->route('caretaker.crew.index', ['with' => $with, 'tab' => 'dm'])
            : redirect()->route('caretaker.crew.index');
    }

    public function respond(Request $request, Confrontation $confrontation): RedirectResponse
    {
        $me = $request->user()->staff;

        if ((int) $confrontation->accused_staff_id !== (int) $me->id) {
            abort(403);
        }

        if ($confrontation->manager_verdict) {
            session()->flash('error', 'A verdict has already been reached on that one.');

            return redirect()->route('caretaker.crew.index');
        }

        $response = (string) $request->validate(['response' => ['required', 'in:confessed,innocent,bs']])['response'];

        app(CrewChatEngine::class)->respondToConfrontation($me, $confrontation, $response);

        session()->flash('success', 'Response recorded. The manager will weigh in.');

        return redirect()->route('caretaker.crew.index', ['tab' => 'reported']);
    }

    public function vent(Request $request)
    {
        $me = $request->user()->staff;

        if (! $me || ! $me->is_active) {
            abort(403);
        }

        $result = app(CrewChatEngine::class)->vent($me);

        if ($result['snitched']) {
            session()->flash('error', 'You vented... and ' . ($result['snitch_name'] ?? 'somebody') . ' snitched on you. Expect a confrontation.');
        } else {
            $listeners = count($result['heard_by']) > 0 ? implode(', ', array_slice($result['heard_by'], 0, 3)) . (count($result['heard_by']) > 3 ? '...' : '') : 'no one';
            session()->flash('success', 'You got that off your chest. ' . ucfirst($listeners) . ' was nearby and heard it.');
        }

        return redirect()->route('caretaker.crew.index');
    }

    private function payload(StaffMessage $message, Staff $me): array
    {
        $staff = $message->staff;

        return [
            'id' => $message->id,
            'mine' => (int) $message->staff_id === (int) $me->id,
            'name' => $staff->user->name ?? 'Crew',
            'initials' => app(CrewChatEngine::class)->initials($staff),
            'bubble_type' => $message->bubble_type,
            'kind' => $message->kind,
            'body' => $message->body,
            'seen_at' => $message->seen_at?->toISOString(),
        ];
    }
}
