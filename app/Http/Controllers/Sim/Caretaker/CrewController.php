<?php

namespace App\Http\Controllers\Sim\Caretaker;

use App\Http\Controllers\Controller;
use App\Models\Confrontation;
use App\Models\StaffRelationship;
use Illuminate\Http\Request;

class CrewController extends Controller
{
    public function index(Request $request)
    {
        $me = $request->user()->staff;

        $relationships = StaffRelationship::with('staffA.user', 'staffB.user')
            ->where('staff_a_id', $me->id)
            ->orWhere('staff_b_id', $me->id)
            ->get();

        $confrontations = Confrontation::with('reporter.user', 'accused.user')
            ->where('reporter_staff_id', $me->id)
            ->orWhere('accused_staff_id', $me->id)
            ->orderByDesc('created_at')
            ->get();

        return view('sim.caretaker.crew.index', compact('me', 'relationships', 'confrontations'));
    }
}
