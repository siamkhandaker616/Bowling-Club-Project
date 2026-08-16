<?php

namespace App\Http\Controllers\Sim\Caretaker;

use App\Http\Controllers\Controller;
use App\Models\Fixture;
use App\Models\Inventory;
use App\Services\Simulation\Clock;
use App\Services\Simulation\MatchService;
use Illuminate\Http\Request;

class PrepController extends Controller
{
    public function __construct(private MatchService $matches)
    {
    }

    public function index()
    {
        $today = Clock::date();

        $fixtures = $this->matches->prepWindowFixtures($today)->map(fn ($f) => [
            'fixture' => $f,
            'ready' => $this->matches->readiness($f),
        ]);

        $lowStock = Inventory::whereColumn('quantity', '<=', 'reorder_threshold')->orderBy('name')->get();

        return view('sim.caretaker.prep.index', compact('today', 'fixtures', 'lowStock'));
    }

    public function prepare(Request $request, Fixture $fixture, string $kind)
    {
        $staff = $request->user()->staff;
        if (! $staff || ! $staff->is_active) {
            abort(403);
        }

        if (! in_array($kind, ['kits', 'lane', 'training'], true)) {
            abort(404);
        }

        if ($fixture->status !== 'upcoming') {
            session()->flash('error', 'This fixture is no longer upcoming.');

            return redirect()->route('caretaker.prep.index');
        }

        $result = match ($kind) {
            'kits' => $this->matches->prepKits($fixture, $staff->id),
            'lane' => $this->matches->prepLane($fixture, $staff->id),
            'training' => $this->matches->prepTraining($fixture, $staff->id),
        };

        session()->flash($result['ok'] ? 'success' : 'error', $result['message']);

        return redirect()->route('caretaker.prep.index');
    }
}
