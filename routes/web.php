<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SocialiteController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Role-specific dashboards
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/', App\Http\Controllers\Manager\DashboardController::class)->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role:steward'])->prefix('steward')->name('steward.')->group(function () {
    Route::get('/', App\Http\Controllers\Steward\DashboardController::class)->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role:caretaker'])->prefix('caretaker')->name('caretaker.')->group(function () {
    Route::get('/', App\Http\Controllers\Caretaker\DashboardController::class)->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role:customer'])->prefix('visitor')->name('visitor.')->group(function () {
    Route::get('/', App\Http\Controllers\Customer\DashboardController::class)->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/auth/google/redirect', [SocialiteController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [SocialiteController::class, 'callback'])->name('google.callback');

Route::view('/privacy-policy', 'legal.privacy')->name('privacy.policy');
Route::view('/terms-of-service', 'legal.terms')->name('terms.service');

require __DIR__.'/auth.php';
