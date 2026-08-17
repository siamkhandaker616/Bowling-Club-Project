<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\FacilityZone;
use App\Models\Lane;

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

        $lanes = Lane::select('id', 'lane_number', 'status', 'oil_level', 'last_maintained_at')
            ->orderBy('lane_number')
            ->get();

        $bagCount = (int) CartItem::where('session_id', session()->getId())->sum('quantity');

        return view('site.facility-map', compact('zones', 'lanes', 'bagCount'));
    }
}
