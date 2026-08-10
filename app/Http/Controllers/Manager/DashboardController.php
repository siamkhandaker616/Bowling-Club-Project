<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total_staff'   => \App\Models\Staff::count(),
            'total_lanes'   => \App\Models\Lane::count(),
            'total_leagues' => \App\Models\League::count(),
            'active_staff'  => \App\Models\Staff::where('is_active', true)->count(),
        ];

        return view('dashboards.manager', compact('user', 'stats'));
    }
}
