<?php

namespace App\Http\Controllers\Sim\Manager;

use App\Http\Controllers\Controller;
use App\Models\ClubConfig;
use App\Services\Simulation\DayCycle;
use Illuminate\Http\Request;

class DayController extends Controller
{
    public function __construct(private DayCycle $dayCycle)
    {
    }

    public function stats()
    {
        $cfg = ClubConfig::singleton();
        return response()->json([
            'current_day' => $cfg->current_day,
            'bad_day_mode' => $cfg->bad_day_mode,
        ]);
    }

    public function advance(Request $request)
    {
        $log = $this->dayCycle->advance();

        session()->flash('day_report', $log);

        $count = count($log['accidents']);
        $quits = $log['quits'] ?? 0;
        $snitches = count($log['snitches'] ?? []);

        $flavor = [];
        if ($count > 0) {
            $flavor[] = "{$count} accident(s) on the floor";
        }
        if ($quits > 0) {
            $flavor[] = "{$quits} staff walked out";
        }
        if ($snitches > 0) {
            $flavor[] = "{$snitches} snitch report(s) filed";
        }

        session()->flash('success', "Day {$log['date_label']} wrapped. {$log['bookings_served']} lanes served, \${$log['revenue']} in revenue."
            . ($flavor ? ' ' . implode(', ', $flavor) . '.' : ''));

        return redirect()->route('manager.dashboard');
    }

    public function toggleBadDay(Request $request)
    {
        $cfg = ClubConfig::singleton();
        $cfg->bad_day_mode = ! $cfg->bad_day_mode;
        $cfg->save();

        session()->flash('success', $cfg->bad_day_mode ? 'Bad Day mode ON — expect trouble tomorrow.' : 'Bad Day mode OFF.');

        return redirect()->back();
    }
}
