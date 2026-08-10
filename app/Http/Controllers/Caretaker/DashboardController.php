<?php

namespace App\Http\Controllers\Caretaker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Lane;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $staff = $user->staff;

        $personalities = $staff ? $staff->personalities : collect();

        $stats = [
            'total_lanes' => Lane::count(),
        ];

        return view('dashboards.caretaker', compact('user', 'staff', 'personalities', 'stats'));
    }
}
