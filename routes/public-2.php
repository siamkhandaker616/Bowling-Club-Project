<?php

use App\Http\Controllers\PublicPortal\EventController;
use App\Http\Controllers\PublicPortal\FixtureController;
use App\Http\Controllers\PublicPortal\PaymentController;
use App\Http\Controllers\PublicPortal\StatController;
use App\Http\Controllers\PublicPortal\TouringController;
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

Route::get('/stats', [StatController::class, 'index'])->name('public.stats');

Route::get('/touring', [TouringController::class, 'create'])->name('public.touring');
Route::post('/touring', [TouringController::class, 'store'])->name('public.touring.store');
Route::get('/touring/welcome/{touringRequest}', [TouringController::class, 'welcome'])->name('public.touring.welcome');

Route::get('/events', [EventController::class, 'index'])->name('public.events');
Route::get('/events/{event}', [EventController::class, 'show'])->name('public.events.show');
Route::post('/events/{event}/rsvp', [EventController::class, 'rsvp'])->name('public.events.rsvp');

Route::get('/pay/{payment}/success', [PaymentController::class, 'success'])->name('public.pay.success');
Route::get('/pay/{payment}/fail', [PaymentController::class, 'fail'])->name('public.pay.fail');
Route::get('/pay/{payment}/cancel', [PaymentController::class, 'cancel'])->name('public.pay.cancel');
Route::post('/pay/ipn', [PaymentController::class, 'ipn'])
    ->name('public.pay.ipn')
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
