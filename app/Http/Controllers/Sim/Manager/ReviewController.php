<?php

namespace App\Http\Controllers\Sim\Manager;

use App\Http\Controllers\Controller;
use App\Models\StaffReview;
use App\Models\VisitorReview;

class ReviewController extends Controller
{
    public function index()
    {
        $visitorReviews = VisitorReview::with('visitor', 'booking.lane')->orderByDesc('created_at')->get();

        $staffReviews = StaffReview::with('staff.user', 'visitor', 'booking.lane')->orderByDesc('created_at')->get();

        return view('sim.manager.reviews.index', compact('visitorReviews', 'staffReviews'));
    }
}
