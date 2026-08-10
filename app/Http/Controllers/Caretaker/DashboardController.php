<?php

namespace App\Http\Controllers\Caretaker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $staff = $user->staff;

        $personalities = $staff ? $staff->personalities : collect();

        return view('dashboards.caretaker', compact('user', 'staff', 'personalities'));
    }
}
