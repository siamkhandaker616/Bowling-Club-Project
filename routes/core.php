<?php

use App\Http\Controllers\PublicPortal\EventController;
use App\Http\Controllers\PublicPortal\FixtureController;
use App\Http\Controllers\PublicPortal\PaymentController;
use App\Http\Controllers\PublicPortal\ProShopController;
use App\Http\Controllers\PublicPortal\StatController;
use App\Http\Controllers\PublicPortal\TouringController;
use App\Http\Controllers\PublicSite\AnnouncementController;
use App\Http\Controllers\PublicSite\FacilityMapController;
use App\Http\Controllers\PublicSite\SnackbarController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| LAYER A — PUBLIC ROUTES (merged core)
|--------------------------------------------------------------------------
| Shared ownership: A_1 (Public Site Core) + A_2 (Public Data & Transactions).
| Formerly `routes/public-1.php` (A_1) and `routes/public-2.php` (A_2);
| merged by the coordinator 2026-08-14 into this single file.
|
| Name prefixes: A_1 = `site.*` (+ `home`), A_2 = `public.*` + `public.pay.*`.
| Keep these prefixes and unique route URIs app-wide.
| Non-public routes stay in their existing files (web.php, sim.php, game.php).
| Loaded under the `web` middleware (see bootstrap/app.php).
*/

/*
|--------------------------------------------------------------------------
| LAYER A_1 — PUBLIC SITE CORE ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $bagCount = (int) \App\Models\CartItem::where('session_id', session()->getId())->sum('quantity');
    return view('welcome', compact('bagCount'));
})->name('home');

Route::get('/api/lanes', function () {
    $lanes = \App\Models\Lane::select('id', 'lane_number', 'status', 'oil_level', 'last_maintained_at')->orderBy('lane_number')->get();
    return response()->json($lanes);
})->name('site.lanes.api');

Route::get('/facility-map', [FacilityMapController::class, 'index'])->name('site.facility-map');

Route::get('/snackbar', [SnackbarController::class, 'index'])->name('site.snackbar');

Route::prefix('admin')->name('site.announcements.')->middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('index');
    Route::get('/announcements/create', [AnnouncementController::class, 'create'])->name('create');
    Route::post('/announcements', [AnnouncementController::class, 'store'])->name('store');
    Route::get('/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('edit');
    Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('update');
    Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('destroy');
});

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
|   - Add ALL public portal routes here, never in routes/web.php.
|   - Do not reuse route names: home, login, register, logout, dashboard,
|     profile.*, password.*, verification.*, google.*, manager.*,
|     steward.*, caretaker.*, visitor.*, site.*, sim.*, game.*
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

Route::get('/pro-shop', [ProShopController::class, 'index'])->name('public.proshop.index');
Route::get('/pro-shop/cart', [ProShopController::class, 'cart'])->name('public.proshop.cart');
Route::post('/pro-shop/cart/add', [ProShopController::class, 'add'])->name('public.proshop.cart.add');
Route::post('/pro-shop/cart/update', [ProShopController::class, 'update'])->name('public.proshop.cart.update');
Route::post('/pro-shop/cart/remove', [ProShopController::class, 'remove'])->name('public.proshop.cart.remove');
Route::post('/pro-shop/checkout', [ProShopController::class, 'checkout'])->name('public.proshop.checkout');
