<?php

namespace App\Http\Controllers\PublicPortal;

use App\Http\Controllers\Controller;
use App\Mail\TouringWelcome;
use App\Models\Club;
use App\Models\TouringRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class TouringController extends Controller
{
    public function create(): View
    {
        return view('portal.touring.index', [
            'club' => Club::first(),
            'amenities' => $this->amenities(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'team_name' => ['required', 'string', 'max:120'],
            'home_club' => ['nullable', 'string', 'max:120'],
            'arrival_date' => ['required', 'date', 'after_or_equal:today'],
            'player_count' => ['required', 'integer', 'min:1', 'max:24'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $touring = TouringRequest::create($data);

        $club = Club::first();

        if ($club && $club->email) {
            Mail::to($club->email)->send(new TouringWelcome($touring, $club));
        }

        session()->flash('success', 'Request sent! Welcome pack is ready to print.');

        return redirect()->route('public.touring.welcome', $touring);
    }

    public function welcome(TouringRequest $touringRequest): View
    {
        return view('portal.touring.welcome', [
            'touring' => $touringRequest,
            'club' => Club::first(),
            'amenities' => $this->amenities(),
        ]);
    }

    private function amenities(): array
    {
        return [
            ['name' => 'Pro Shop', 'note' => 'Ball drilling, resurfacing, and the latest gear.'],
            ['name' => 'Sports Bar & Lounge', 'note' => 'Cold pours and big screens between frames.'],
            ['name' => 'Arcade Corner', 'note' => 'Classic cabinets for the team off-hours.'],
            ['name' => 'Locker Rooms', 'note' => 'Secure storage for gear and bags.'],
            ['name' => 'Free Parking', 'note' => 'On-site lot with 40+ spaces for team buses.'],
        ];
    }
}
