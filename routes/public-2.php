<?php

use App\Http\Controllers\PublicPortal\FixtureController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| LAYER A_2 — PUBLIC PORTAL ROUTES
|--------------------------------------------------------------------------
| Owned by the Layer A_2 (Public Data & Transactions) build session.
| Data-heavy pages + forms + payments: Dynamic Fixture & Results (F2),
| Season Statistics (F7), Touring Welcome Portal (F4), Events Hub +
| RSVP with Capacity (F5/F9), and the SSL Commerz payment flow.
|
| Rules:
|   - Add ALL A_2 routes here, never in routes/web.php.
|   - Do not reuse route names: home, login, register, logout, dashboard,
|     profile.*, password.*, verification.*, google.*, manager.*,
|     steward.*, caretaker.*, visitor.*, site.*, sim.*, game.*
|   - This file is loaded under the `web` middleware (see bootstrap/app.php).
*/

Route::get('/fixtures', [FixtureController::class, 'index'])->name('public.fixtures');
