<?php

namespace App\Http\Controllers\Sim\Caretaker;

use App\Http\Controllers\Controller;
use App\Models\Lane;
use Illuminate\Http\Request;

class LaneController extends Controller
{
    public function maintain(Request $request, Lane $lane)
    {
        $data = $request->validate([
            'action' => ['required', 'in:oiled,cleaned,toggle_maint'],
        ]);

        switch ($data['action']) {
            case 'oiled':
                $lane->update(['oil_level' => 100, 'last_maintained_at' => now()]);
                session()->flash('success', 'Lane ' . $lane->lane_number . ' oiled and re-surfaced.');
                break;

            case 'cleaned':
                $lane->update(['last_maintained_at' => now()]);
                session()->flash('success', 'Lane ' . $lane->lane_number . ' cleaned and logged.');
                break;

            case 'toggle_maint':
                if (! in_array($lane->status, ['open', 'maintenance'])) {
                    session()->flash('error', 'Lane ' . $lane->lane_number . ' is currently ' . $lane->status . ' — toggle maintenance from the caretaker desk.');

                    return redirect()->back();
                }

                $lane->update(['status' => $lane->status === 'maintenance' ? 'open' : 'maintenance']);
                session()->flash('success', 'Lane ' . $lane->lane_number . ' is now ' . $lane->status . '.');
                break;
        }

        return redirect()->back();
    }
}
