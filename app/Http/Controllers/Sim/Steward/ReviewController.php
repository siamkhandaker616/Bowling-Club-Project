<?php

namespace App\Http\Controllers\Sim\Steward;

use App\Http\Controllers\Controller;
use App\Models\LaneBooking;
use App\Models\StaffReview;
use App\Models\Visitor;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Visitor $visitor)
    {
        $staff = $request->user()->staff;
        if (! $staff || ! $staff->is_active) {
            abort(403);
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'body' => ['nullable', 'string', 'max:1000'],
            'booking_id' => ['required', 'exists:lane_bookings,id'],
            'was_polite' => ['nullable', 'boolean'],
            'caused_issues' => ['nullable', 'boolean'],
        ]);

        $booking = LaneBooking::where('id', $data['booking_id'])
            ->where('visitor_id', $visitor->id)
            ->where('status', 'completed')
            ->firstOrFail();

        StaffReview::create([
            'staff_id' => $staff->id,
            'visitor_id' => $visitor->id,
            'booking_id' => $booking->id,
            'rating' => $data['rating'],
            'body' => $data['body'] ?? null,
            'was_polite' => $request->boolean('was_polite'),
            'caused_issues' => $request->boolean('caused_issues'),
        ]);

        $avg = StaffReview::where('visitor_id', $visitor->id)->avg('rating');

        $visitor->reputation_score = max(0, min(100, (int) round(($avg ?: 0) * 20)));
        $visitor->save();

        session()->flash('success', $visitor->name . ' rated ' . $data['rating'] . '/5. Reputation now ' . $visitor->reputation_score . '.');

        return redirect()->route('steward.visitors.index');
    }
}
