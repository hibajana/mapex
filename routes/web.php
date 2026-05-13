<?php

use App\Http\Controllers\ItineraryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', fn() => view('welcome'))->name('home');

// Auth (Laravel Breeze / Fortify handles these)
require __DIR__.'/auth.php';

// Protected routes
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Planner form
    Route::get('/planner', fn() => view('planner.index'))->name('planner');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Itinerary views
    Route::get('/itinerary/{itinerary}', [ItineraryController::class, 'show'])->name('itinerary.show');
    Route::get('/history', fn() => view('history.index'))->name('history.index');

    // API-style JSON endpoints (same domain, CSRF protected)
    Route::prefix('api')->middleware('throttle:30,1')->group(function () {
        Route::post('/generate-itinerary',           [ItineraryController::class, 'generate'])->name('api.generate');
        Route::post('/regenerate-itinerary/{itinerary}', [ItineraryController::class, 'regenerate'])->name('api.regenerate');
        Route::post('/relax-mode/{itinerary}',       [ItineraryController::class, 'relaxMode'])->name('api.relax');
        Route::post('/eco-mode/{itinerary}',         [ItineraryController::class, 'ecoMode'])->name('api.eco');
        Route::get('/history',                       [ItineraryController::class, 'history'])->name('api.history');
    });
});
