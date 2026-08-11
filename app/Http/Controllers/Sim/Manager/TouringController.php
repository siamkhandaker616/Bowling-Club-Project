<?php

namespace App\Http\Controllers\Sim\Manager;

use App\Http\Controllers\Controller;
use App\Models\TouringRequest;
use Illuminate\Http\Request;

class TouringController extends Controller
{
    public function index()
    {
        $requests = TouringRequest::orderBy('status')->orderByDesc('arrival_date')->get();

        return view('sim.manager.touring.index', compact('requests'));
    }

    public function confirm(Request $request, TouringRequest $touringRequest)
    {
        if ($touringRequest->status !== 'pending') {
            session()->flash('error', 'Request already decided.');

            return redirect()->route('manager.touring.index');
        }

        $touringRequest->update(['status' => 'confirmed']);

        session()->flash('success', $touringRequest->team_name . ' confirmed for ' . $touringRequest->arrival_date . '.');

        return redirect()->route('manager.touring.index');
    }

    public function decline(Request $request, TouringRequest $touringRequest)
    {
        if ($touringRequest->status !== 'pending') {
            session()->flash('error', 'Request already decided.');

            return redirect()->route('manager.touring.index');
        }

        $touringRequest->update(['status' => 'declined']);

        session()->flash('success', $touringRequest->team_name . ' request declined.');

        return redirect()->route('manager.touring.index');
    }
}
