<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DroneController;

/*
|--------------------------------------------------------------------------
| Agri-Trek API Routes
|--------------------------------------------------------------------------
| These routes are intended for drone hardware or external integrations.
| All routes are prefixed with /api automatically by Laravel.
|
| Example Usage:
|   GET  /api/drones               - List all drones with latest telemetry
|   POST /api/drones/{id}/log      - Push new telemetry data from a drone
|   GET  /api/drones/{id}/path     - Get route history as GeoJSON
|--------------------------------------------------------------------------
*/

Route::prefix('drones')->group(function () {
    Route::get('/',              [DroneController::class, 'apiIndex']);
    Route::post('/{id}/log',     [DroneController::class, 'apiLog']);
    Route::get('/{id}/path',     [DroneController::class, 'apiPath']);
});
