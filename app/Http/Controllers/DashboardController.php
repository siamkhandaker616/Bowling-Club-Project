<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        return match ($user->role) {
            'admin'     => redirect()->route('manager.dashboard'),
            'steward'   => redirect()->route('steward.dashboard'),
            'caretaker' => redirect()->route('caretaker.dashboard'),
            'customer'  => redirect()->route('visitor.dashboard'),
            default     => redirect()->route('home'),
        };
    }
}
