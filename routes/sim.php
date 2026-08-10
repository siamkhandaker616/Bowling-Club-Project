<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| LAYER B — SIMULATION ROUTES
|--------------------------------------------------------------------------
| Owned by the Layer B (Simulation) build session.
| Feature 11 (Manager Mode / Staff Management Sim) and Feature 12
| (Client / Visitor Mode): day-cycle, staff, shifts, schedules, inventory,
| complaints/compensation, reviews, bans, queues, confrontations.
|
| Rules:
|   - Add ALL simulation routes here, never in routes/web.php.
|   - Role-dashboard prefix groups (`/manager`, `/steward`, `/caretaker`,
|     `/visitor`) already have their shell routes in routes/web.php. Add
|     sub-routes here with the same prefix and re-apply auth middleware:
|         Route::middleware(['auth', 'verified', 'role:admin'])
|             ->prefix('manager')->name('manager.')->group(...)
|   - Do not reuse Breeze route names (see routes/public.php header).
|   - This file is loaded under the `web` middleware (see bootstrap/app.php).
*/
