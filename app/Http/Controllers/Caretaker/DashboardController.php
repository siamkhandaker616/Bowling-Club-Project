<?php

namespace App\Http\Controllers\Caretaker;

use App\Http\Controllers\Controller;
use App\Models\Confrontation;
use App\Models\Inventory;
use App\Models\Lane;
use App\Models\Shift;
use App\Models\StaffRelationship;
use App\Services\Simulation\Clock;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $staff = $user->staff;

        $date = Clock::date();

        $myShifts = $staff
            ? Shift::with('lane')->where('staff_id', $staff->id)->whereDate('date', $date)->get()
            : collect();

        $lanes = Lane::orderBy('lane_number')->get();

        $stock = Inventory::orderBy('category')->orderBy('name')->get();

        $relationships = $staff
            ? StaffRelationship::with('staffA.user', 'staffB.user')
                ->where('staff_a_id', $staff->id)
                ->orWhere('staff_b_id', $staff->id)
                ->get()
            : collect();

        $confrontations = $staff
            ? Confrontation::with('reporter.user', 'accused.user')
                ->where('reporter_staff_id', $staff->id)
                ->orWhere('accused_staff_id', $staff->id)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
            : collect();

        $personalities = $staff ? $staff->personalities : collect();

        $stats = [
            'total_lanes' => Lane::count(),
            'low_oil_lanes' => $lanes->where('oil_level', '<', 20)->count(),
            'completed_shifts' => $myShifts->where('status', 'completed')->count(),
            'low_stock_items' => $stock->filter(fn ($i) => $i->isLowStock())->count(),
        ];

        return view('dashboards.caretaker', compact('user', 'staff', 'personalities', 'stats', 'date', 'myShifts', 'lanes', 'stock', 'relationships', 'confrontations'));
    }
}
