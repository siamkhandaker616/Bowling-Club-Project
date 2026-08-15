<?php

namespace App\Http\Controllers\Sim\Steward;

use App\Http\Controllers\Controller;
use App\Models\BanRequest;
use App\Models\Visitor;
use App\Services\Simulation\Clock;
use Illuminate\Http\Request;

class BanRequestController extends Controller
{
    public function index()
    {
        $requests = BanRequest::with('visitor', 'requester.user')->orderByDesc('created_at')->get();

        $visitors = Visitor::where('is_banned', false)->orderBy('name')->get();

        return view('sim.steward.bans.index', compact('requests', 'visitors'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'visitor_id' => ['required', 'exists:visitors,id'],
            'reason' => ['required', 'string', 'max:1000'],
            'evidence' => ['nullable', 'string', 'max:1000'],
        ]);

        BanRequest::create([
            'visitor_id' => $data['visitor_id'],
            'requested_by_staff_id' => $request->user()->staff->id,
            'reason' => $data['reason'],
            'evidence' => $data['evidence'] ?? null,
            'status' => 'pending',
        ]);

        session()->flash('success', 'Ban request submitted for manager review.');

        return redirect()->route('steward.bans.index');
    }
}
