<?php

namespace App\Http\Controllers\Sim\Manager;

use App\Http\Controllers\Controller;
use App\Models\BanRequest;
use App\Models\Visitor;
use Illuminate\Http\Request;

class BanController extends Controller
{
    public function index()
    {
        $requests = BanRequest::with('visitor', 'requester.user')->orderBy('status')->orderByDesc('created_at')->get();

        $banned = Visitor::where('is_banned', true)->with('user')->get();

        return view('sim.manager.bans.index', compact('requests', 'banned'));
    }

    public function approve(Request $request, BanRequest $banRequest)
    {
        if ($banRequest->status !== 'pending') {
            session()->flash('error', 'This request was already decided.');

            return redirect()->route('manager.bans.index');
        }

        $banRequest->status = 'approved';
        $banRequest->reviewed_by_admin_id = $request->user()->id;
        $banRequest->reviewed_at = now();
        $banRequest->admin_notes = $request->get('notes');
        $banRequest->save();

        $banRequest->visitor->update([
            'is_banned' => true,
            'ban_reason' => $banRequest->reason,
            'banned_by_admin_id' => $request->user()->id,
            'banned_at' => now(),
        ]);

        session()->flash('success', $banRequest->visitor->name . ' banned.');

        return redirect()->route('manager.bans.index');
    }

    public function deny(Request $request, BanRequest $banRequest)
    {
        if ($banRequest->status !== 'pending') {
            session()->flash('error', 'This request was already decided.');

            return redirect()->route('manager.bans.index');
        }

        $banRequest->status = 'denied';
        $banRequest->reviewed_by_admin_id = $request->user()->id;
        $banRequest->reviewed_at = now();
        $banRequest->admin_notes = $request->get('notes');
        $banRequest->save();

        session()->flash('success', 'Ban request denied.');

        return redirect()->route('manager.bans.index');
    }
}
