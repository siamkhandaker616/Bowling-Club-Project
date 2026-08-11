<?php

namespace App\Http\Controllers\Sim\Visitor;

use App\Http\Controllers\Controller;
use App\Models\LaneBooking;
use App\Models\ReviewVote;
use App\Models\StaffReview;
use App\Models\Visitor;
use App\Models\VisitorReview;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $visitor = Visitor::where('user_id', $request->user()->id)->first();

        $allReviews = VisitorReview::with('visitor', 'booking.lane')->orderByDesc('created_at')->limit(30)->get();

        $mine = $visitor
            ? VisitorReview::where('visitor_id', $visitor->id)->get()
            : collect();

        $completedBookings = $visitor
            ? LaneBooking::with('lane')->where('visitor_id', $visitor->id)->where('status', 'completed')->get()
            : collect();

        $votedReviewIds = $visitor
            ? ReviewVote::where('voter_id', $request->user()->id)->pluck('review_id')->all()
            : [];

        $staff = \App\Models\Staff::with('user')->where('is_active', true)->get();

        return view('sim.visitor.reviews.index', compact('allReviews', 'mine', 'completedBookings', 'votedReviewIds', 'staff'));
    }

    public function store(Request $request, LaneBooking $booking)
    {
        $visitor = Visitor::where('user_id', $request->user()->id)->first();

        if (! $visitor || $booking->visitor_id !== $visitor->id) {
            abort(403);
        }

        if ($booking->status !== 'completed') {
            session()->flash('error', 'You can only review completed bookings.');

            return redirect()->route('visitor.reviews.index');
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'body' => ['nullable', 'string', 'max:1000'],
        ]);

        VisitorReview::create([
            'visitor_id' => $visitor->id,
            'booking_id' => $booking->id,
            'rating' => $data['rating'],
            'body' => $data['body'] ?? null,
        ]);

        if ($request->has('staff_id')) {
            $request->validate(['staff_id' => ['required', 'exists:staff,id']]);

            StaffReview::create([
                'staff_id' => $request->staff_id,
                'visitor_id' => $visitor->id,
                'booking_id' => $booking->id,
                'rating' => $data['rating'],
                'body' => $data['body'] ?? null,
            ]);
        }

        session()->flash('success', 'Thanks for your review!');

        return redirect()->route('visitor.reviews.index');
    }

    public function vote(Request $request, VisitorReview $review)
    {
        $data = $request->validate([
            'vote' => ['required', 'in:helpful,not_helpful'],
        ]);

        $existing = ReviewVote::where('review_id', $review->id)
            ->where('review_type', 'visitor_review')
            ->where('voter_id', $request->user()->id)
            ->first();

        if ($existing) {
            $existing->update(['vote' => $data['vote']]);
        } else {
            ReviewVote::create([
                'review_id' => $review->id,
                'review_type' => 'visitor_review',
                'voter_id' => $request->user()->id,
                'vote' => $data['vote'],
            ]);
        }

        $review->helpful_count = ReviewVote::where('review_id', $review->id)->where('review_type', 'visitor_review')->where('vote', 'helpful')->count();
        $review->not_helpful_count = ReviewVote::where('review_id', $review->id)->where('review_type', 'visitor_review')->where('vote', 'not_helpful')->count();
        $review->save();

        return redirect()->route('visitor.reviews.index');
    }
}
