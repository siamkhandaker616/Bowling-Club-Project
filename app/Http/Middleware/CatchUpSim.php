<?php

namespace App\Http\Middleware;

use App\Models\ClubConfig;
use App\Services\Simulation\DayCycle;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CatchUpSim
{
    private const MAX_CATCH_UP_DAYS = 14;

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        $cfg = ClubConfig::singleton();

        if (! $cfg->last_advanced_at) {
            $cfg->last_advanced_at = now();
            $cfg->save();

            return $next($request);
        }

        $elapsedDays = (int) floor(abs(now()->diffInSeconds($cfg->last_advanced_at)) / 86400);

        if ($elapsedDays >= 1) {
            $dayCycle = app(DayCycle::class);
            $steps = min($elapsedDays, self::MAX_CATCH_UP_DAYS);

            for ($i = 0; $i < $steps; $i++) {
                $dayCycle->advance();
            }
        }

        return $next($request);
    }
}
