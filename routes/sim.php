<?php

use App\Http\Controllers\Sim\Manager\BanController;
use App\Http\Controllers\Sim\Manager\BookingController;
use App\Http\Controllers\Sim\Manager\ComplaintController;
use App\Http\Controllers\Sim\Manager\ConfrontationController;
use App\Http\Controllers\Sim\Manager\DayController;
use App\Http\Controllers\Sim\Manager\InventoryController;
use App\Http\Controllers\Sim\Manager\LeagueController;
use App\Http\Controllers\Sim\Manager\ReviewController;
use App\Http\Controllers\Sim\Manager\StaffController;
use App\Http\Controllers\Sim\Manager\TouringController;
use App\Http\Controllers\Sim\Steward\BanRequestController;
use App\Http\Controllers\Sim\Steward\ComplaintController as StewardComplaintController;
use App\Http\Controllers\Sim\Steward\ScheduleController as StewardScheduleController;
use App\Http\Controllers\Sim\Steward\SnitchController;
use App\Http\Controllers\Sim\Steward\ReviewController as StewardReviewController;
use App\Http\Controllers\Sim\Steward\VisitorController;
use App\Http\Controllers\Sim\Caretaker\CrewController;
use App\Http\Controllers\Sim\Caretaker\InventoryController as CaretakerInventoryController;
use App\Http\Controllers\Sim\Caretaker\LaneController;
use App\Http\Controllers\Sim\Caretaker\PrepController;
use App\Http\Controllers\Sim\Caretaker\ShiftController;
use App\Http\Controllers\Sim\Visitor\BookingController as VisitorBookingController;
use App\Http\Controllers\Sim\Visitor\ComplaintController as VisitorComplaintController;
use App\Http\Controllers\Sim\Visitor\QueueController;
use App\Http\Controllers\Sim\Visitor\ReviewController as VisitorReviewController;
use App\Http\Controllers\Sim\ReapplyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| LAYER B — SIMULATION ROUTES
|--------------------------------------------------------------------------
| Feature 11 (Manager Mode / Staff Management Sim) + Feature 12
| (Client / Visitor Mode). Names follow their role prefix so the existing
| dashboard navigation highlighting (routeIs('manager.*') etc.) keeps
| working. All names live under the sim layer via these prefixes.
*/

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/day/stats', [DayController::class, 'stats'])->name('day.stats');
    Route::post('/day/advance', [DayController::class, 'advance'])->name('day.advance');
    Route::post('/day/toggle-bad-day', [DayController::class, 'toggleBadDay'])->name('day.toggleBadDay');

    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::get('/staff/{staff}', [StaffController::class, 'show'])->name('staff.show');
    Route::get('/staff/{staff}/edit', [StaffController::class, 'edit'])->name('staff.edit');
    Route::put('/staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');
    Route::post('/staff/{staff}/bonus', [StaffController::class, 'bonus'])->name('staff.bonus');
    Route::post('/staff/{staff}/penalty', [StaffController::class, 'penalty'])->name('staff.penalty');

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/{inventory}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{inventory}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::post('/inventory/{inventory}/restock', [InventoryController::class, 'restock'])->name('inventory.restock');
    Route::post('/inventory/{inventory}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::delete('/inventory/{inventory}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

    Route::get('/bans', [BanController::class, 'index'])->name('bans.index');
    Route::post('/bans/{banRequest}/approve', [BanController::class, 'approve'])->name('bans.approve');
    Route::post('/bans/{banRequest}/deny', [BanController::class, 'deny'])->name('bans.deny');

    Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
    Route::post('/complaints/{complaint}/resolve', [ComplaintController::class, 'resolve'])->name('complaints.resolve');
    Route::post('/complaints/{complaint}/dismiss', [ComplaintController::class, 'dismiss'])->name('complaints.dismiss');

    Route::get('/confrontations', [ConfrontationController::class, 'index'])->name('confrontations.index');
    Route::post('/confrontations', [ConfrontationController::class, 'store'])->name('confrontations.store');
    Route::post('/confrontations/{confrontation}/respond', [ConfrontationController::class, 'respond'])->name('confrontations.respond');
    Route::post('/confrontations/{confrontation}/verdict', [ConfrontationController::class, 'verdict'])->name('confrontations.verdict');

    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');

    Route::get('/touring', [TouringController::class, 'index'])->name('touring.index');
    Route::post('/touring/{touringRequest}/confirm', [TouringController::class, 'confirm'])->name('touring.confirm');
    Route::post('/touring/{touringRequest}/decline', [TouringController::class, 'decline'])->name('touring.decline');

    Route::get('/league', [LeagueController::class, 'index'])->name('league.index');
    Route::post('/league/{fixture}/welcome', [LeagueController::class, 'welcome'])->name('league.welcome');
});

Route::middleware(['auth', 'verified', 'role:steward'])->prefix('steward')->name('steward.')->group(function () {
    Route::get('/schedule', [StewardScheduleController::class, 'index'])->name('schedule.index');
    Route::post('/shifts/{shift}/complete', [StewardScheduleController::class, 'complete'])->name('schedule.complete');

    Route::get('/visitors', [VisitorController::class, 'index'])->name('visitors.index');
    Route::post('/visitors/{visitor}/review', [StewardReviewController::class, 'store'])->name('reviews.store');

    Route::get('/bans', [BanRequestController::class, 'index'])->name('bans.index');
    Route::post('/bans', [BanRequestController::class, 'store'])->name('bans.store');

    Route::get('/complaints', [StewardComplaintController::class, 'index'])->name('complaints.index');
    Route::post('/complaints', [StewardComplaintController::class, 'store'])->name('complaints.store');
    Route::post('/complaints/{complaint}/escalate', [StewardComplaintController::class, 'escalate'])->name('complaints.escalate');

    Route::get('/snitch', [SnitchController::class, 'index'])->name('snitch.index');
    Route::post('/snitch/{report}/escalate', [SnitchController::class, 'escalate'])->name('snitch.escalate');
    Route::post('/snitch/{report}/dismiss', [SnitchController::class, 'dismiss'])->name('snitch.dismiss');
});

Route::middleware(['auth', 'verified', 'role:caretaker'])->prefix('caretaker')->name('caretaker.')->group(function () {
    Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
    Route::post('/shifts/{shift}/complete', [ShiftController::class, 'complete'])->name('shifts.complete');
    Route::post('/shifts/{shift}/cancel', [ShiftController::class, 'cancel'])->name('shifts.cancel');

    Route::get('/inventory', [CaretakerInventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/{inventory}/adjust', [CaretakerInventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::post('/inventory/{inventory}/restock', [CaretakerInventoryController::class, 'restock'])->name('inventory.restock');

    Route::get('/crew', [CrewController::class, 'index'])->name('crew.index');
    Route::get('/crew/messages', [CrewController::class, 'poll'])->name('crew.poll');
    Route::get('/crew/dm', [CrewController::class, 'dm'])->name('crew.dm');
    Route::post('/crew/send', [CrewController::class, 'send'])->name('crew.send');
    Route::post('/crew/messages/{message}/reply', [CrewController::class, 'reply'])->name('crew.reply');
    Route::post('/crew/vent', [CrewController::class, 'vent'])->name('crew.vent');
    Route::post('/crew/respond/{confrontation}', [CrewController::class, 'respond'])->name('crew.respond');

    Route::get('/prep', [PrepController::class, 'index'])->name('prep.index');
    Route::post('/prep/{fixture}/{kind}', [PrepController::class, 'prepare'])->name('prep.prepare');
});

// Lane maintenance — caretaker AND admin may run it (A_1's facility-map panel buttons call this).
Route::middleware(['auth', 'verified', 'role:caretaker,admin'])->prefix('caretaker')->name('caretaker.')->group(function () {
    Route::post('/lanes/{lane}/maintenance', [LaneController::class, 'maintain'])->name('lanes.maintain');
});

Route::middleware(['auth', 'verified', 'role:customer'])->prefix('visitor')->name('visitor.')->group(function () {
    Route::get('/book', [VisitorBookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [VisitorBookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings', [VisitorBookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings/{booking}/cancel', [VisitorBookingController::class, 'cancel'])->name('bookings.cancel');

    Route::get('/queue', [QueueController::class, 'index'])->name('queues.index');

    Route::get('/reviews', [VisitorReviewController::class, 'index'])->name('reviews.index');
    Route::post('/bookings/{booking}/review', [VisitorReviewController::class, 'store'])->name('reviews.store');
    Route::post('/reviews/{review}/vote', [VisitorReviewController::class, 'vote'])->name('reviews.vote');

    Route::get('/complaints', [VisitorComplaintController::class, 'index'])->name('complaints.index');
    Route::post('/complaints', [VisitorComplaintController::class, 'store'])->name('complaints.store');
});

Route::middleware(['auth', 'verified'])->name('reapply.')->group(function () {
    Route::get('/reapply', [ReapplyController::class, 'index'])->name('index');
    Route::post('/reapply', [ReapplyController::class, 'store'])->name('store');
});
