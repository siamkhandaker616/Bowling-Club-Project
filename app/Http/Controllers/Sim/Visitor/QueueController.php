<?php

namespace App\Http\Controllers\Sim\Visitor;

use App\Http\Controllers\Controller;
use App\Models\BookingQueue;
use App\Models\Visitor;
use App\Services\Simulation\VisitorRegistry;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function index(Request $request)
    {
        $visitor = app(VisitorRegistry::class)->forUser($request->user());

        $entries = $visitor
            ? BookingQueue::with('booking.lane')
                ->where('visitor_id', $visitor->id)
                ->orderByDesc('created_at')
                ->get()
            : collect();

        return view('sim.visitor.queues.index', compact('entries'));
    }
}
