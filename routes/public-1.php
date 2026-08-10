<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicSite\AnnouncementController;

/*
|--------------------------------------------------------------------------
| LAYER A_1 — PUBLIC SITE CORE ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/api/lanes', function () {
    $lanes = \App\Models\Lane::select('lane_number', 'status', 'oil_level')->orderBy('lane_number')->get();
    return response()->json($lanes);
})->name('site.lanes.api');

Route::prefix('admin')->name('site.announcements.')->middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('index');
    Route::get('/announcements/create', [AnnouncementController::class, 'create'])->name('create');
    Route::post('/announcements', [AnnouncementController::class, 'store'])->name('store');
    Route::get('/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('edit');
    Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('update');
    Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('destroy');
});
