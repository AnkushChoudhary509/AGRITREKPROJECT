<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FarmerController;
use App\Http\Controllers\LandController;
use App\Http\Controllers\SchemeController;
use App\Http\Controllers\DroneController;
use App\Http\Controllers\WaypointController;
use App\Http\Controllers\ClusteringController;
use App\Http\Controllers\VisionController;
use App\Http\Controllers\SensorFusionController;

// ── Public Auth Routes ─────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/',           [AuthController::class, 'showLogin']);
    Route::get('/login',      [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',     [AuthController::class, 'login'])->name('login.submit');

    Route::get('/register',   [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',  [AuthController::class, 'register'])->name('register.submit');

    Route::get('/forgot-password',  [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}',  [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password',         [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ── Authenticated Routes ────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Schemes — all roles
    Route::get('/schemes',                 [SchemeController::class, 'index'])->name('schemes.index');
    Route::get('/schemes/{scheme}',        [SchemeController::class, 'show'])->name('schemes.show');
    Route::post('/schemes/{scheme}/apply', [SchemeController::class, 'apply'])->name('schemes.apply');

    // ── Expert/Admin Routes ──────────────────────────────────────────────
    Route::middleware(['admin'])->group(function () {
        Route::resource('farmers', FarmerController::class);
        Route::resource('lands',   LandController::class);

        // Scheme management
        Route::get('/schemes/create',                      [SchemeController::class, 'create'])->name('schemes.create');
        Route::post('/schemes',                            [SchemeController::class, 'store'])->name('schemes.store');
        Route::get('/schemes/{scheme}/edit',               [SchemeController::class, 'edit'])->name('schemes.edit');
        Route::put('/schemes/{scheme}',                    [SchemeController::class, 'update'])->name('schemes.update');
        Route::delete('/schemes/{scheme}',                 [SchemeController::class, 'destroy'])->name('schemes.destroy');
        Route::get('/scheme-applications',                 [SchemeController::class, 'applications'])->name('schemes.applications');
        Route::patch('/scheme-applications/{application}', [SchemeController::class, 'updateApplication'])->name('schemes.applications.update');

        // Drones
        Route::resource('drones', DroneController::class);
        Route::get('/drones/{drone}/logs', [DroneController::class, 'logs'])->name('drones.logs');

        // Waypoints
        Route::post('/waypoints/simulate',            [WaypointController::class, 'simulate'])->name('waypoints.simulate');
        Route::patch('/waypoints/{waypoint}/reached', [WaypointController::class, 'markReached'])->name('waypoints.reached');
        Route::resource('waypoints', WaypointController::class);

        // Clustering
        Route::get('/clustering',      [ClusteringController::class, 'index'])->name('clustering.index');
        Route::post('/clustering/run', [ClusteringController::class, 'run'])->name('clustering.run');

        // Computer Vision
        Route::get('/vision',          [VisionController::class, 'index'])->name('vision.index');
        Route::post('/vision/analyze', [VisionController::class, 'analyze'])->name('vision.analyze');

        // Sensor Fusion
        Route::get('/sensors', [SensorFusionController::class, 'index'])->name('sensors.index');
    });
});
