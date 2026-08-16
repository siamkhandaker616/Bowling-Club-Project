<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Club;
use App\Models\SnackbarItem;

class SnackbarController extends Controller
{
    public function index()
    {
        $club = Club::first();
        $menu = SnackbarItem::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');
        $bagCount = (int) CartItem::where('session_id', session()->getId())->sum('quantity');

        return view('site.snackbar', compact('club', 'menu', 'bagCount'));
    }
}
