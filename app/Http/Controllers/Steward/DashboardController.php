<?php

namespace App\Http\Controllers\Steward;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        return view('dashboards.steward', compact('user'));
    }
}
