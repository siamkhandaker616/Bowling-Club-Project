<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\FacilityZone;

class FacilityMapController extends Controller
{
    public function index()
    {
        $zones = FacilityZone::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($zone) => [
                'map_key' => $zone->map_key,
                'name' => $zone->name,
                'description' => $zone->description,
                'open_time' => $zone->open_time,
                'close_time' => $zone->close_time,
                'facilities' => $zone->facilities,
            ]);

        return view('site.facility-map', compact('zones'));
    }
}
