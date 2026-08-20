<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Accident;
use App\Models\BanRequest;
use App\Models\ClubConfig;
use App\Models\Complaint;
use App\Models\Confrontation;
use App\Models\Inventory;
use App\Models\Lane;
use App\Models\LaneBooking;
use App\Models\Staff;
use App\Models\StaffEvent;
use App\Models\TouringRequest;
use App\Services\Simulation\Clock;
use App\Services\Simulation\DialogueService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $cfg = ClubConfig::singleton();

        $lanes = Lane::orderBy('lane_number')->get();
        $activeStaff = Staff::with('user')->where('is_active', true)->get();

        $today = Clock::date();

        $stats = [
            'total_staff' => Staff::count(),
            'active_staff' => $activeStaff->count(),
            'avg_happiness' => round($activeStaff->avg('happiness') ?? 0, 1),
            'low_morale' => $activeStaff->where('happiness', '<', 50)->count(),
            'total_lanes' => Lane::count(),
            'busy_lanes' => $lanes->where('status', '!=', 'open')->count(),
            'low_oil_lanes' => $lanes->where('oil_level', '<', 20)->count(),
            'today_bookings' => LaneBooking::whereDate('date', $today)->count(),
            'today_confirmed' => LaneBooking::whereDate('date', $today)->where('status', 'confirmed')->count(),
            'pending_complaints' => Complaint::where('status', 'open')->count(),
            'pending_bans' => BanRequest::where('status', 'pending')->count(),
            'open_confrontations' => Confrontation::whereNull('manager_verdict')->count(),
            'pending_touring' => TouringRequest::where('status', 'pending')->count(),
            'total_revenue' => $cfg->total_revenue,
            'total_expenses' => $cfg->total_expenses,
            'reputation' => $cfg->reputation,
            'current_day' => $cfg->current_day,
            'bad_day_mode' => $cfg->bad_day_mode,
            'net' => $cfg->total_revenue - $cfg->total_expenses,
        ];

        $lowStock = Inventory::all()->filter(fn ($i) => $i->isLowStock());

        $recentEvents = StaffEvent::with('staff.user')->orderByDesc('created_at')->limit(8)->get();

        $recentIncidents = Accident::with('staff.user')->orderByDesc('created_at')->limit(5)->get();

        $dialogue = app(DialogueService::class);
        $chatter = $activeStaff->take(4)->map(fn (Staff $s) => [
            'name' => $s->user->name ?? 'Staff',
            'role' => $s->role,
            'happiness' => $s->happiness,
            'bubbles' => $dialogue->bubblesFor($s, $today),
        ]);

        $dayReport = session('day_report');

        return view('dashboards.manager', compact('user', 'stats', 'lanes', 'lowStock', 'recentEvents', 'dayReport', 'chatter', 'recentIncidents'));
    }
}
