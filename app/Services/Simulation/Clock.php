<?php

namespace App\Services\Simulation;

use App\Models\ClubConfig;
use Carbon\Carbon;

class Clock
{
    public static function timeSlots(): array
    {
        return [
            'morning' => 'Morning (10am - 1pm)',
            'afternoon' => 'Afternoon (1pm - 6pm)',
            'evening' => 'Evening (6pm - 11pm)',
        ];
    }

    public static function date(): Carbon
    {
        $cfg = ClubConfig::singleton();

        return Carbon::today()->addDays(max(0, (int) $cfg->current_day - 1));
    }

    public static function dateForDay(int $day): Carbon
    {
        return Carbon::today()->addDays(max(0, $day - 1));
    }

    public static function label(): string
    {
        return self::date()->format('D, M j Y');
    }
}
