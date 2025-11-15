<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\Api\PublicApiController;

// Public endpoints for dysgraphia platform
Route::get('/doctors/public', [PublicApiController::class, 'getDoctors']);
Route::get('/doctors/public/{doctorId}', [PublicApiController::class, 'getDoctorDetail']);
Route::get('/doctors/public/{doctorId}/available-dates', [PublicApiController::class, 'getAvailableDates']);
Route::get('/doctors/public/{doctorId}/slots/{date}', [PublicApiController::class, 'getAvailableSlots']);
Route::get('/stats/public', [PublicApiController::class, 'getStats']);

// Get providers by city and specialization
Route::get('/providers', [ProviderController::class, 'getProvidersByCityAndSpecialization']);

// Get available dates for a provider
Route::get('/providers/{providerId}/available-dates', [ProviderController::class, 'getAvailableDates']);

// Get available time slots for a specific date
Route::get('/providers/{providerId}/slots', [ProviderController::class, 'getAvailableSlots']);

// Activity Games API (public for guests)
Route::prefix('activities')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\ActivityController::class, 'index']);
    Route::get('/{activity}', [\App\Http\Controllers\Api\ActivityController::class, 'show']);
    Route::post('/{activity}/start', [\App\Http\Controllers\Api\ActivityController::class, 'start']);
    
    Route::prefix('attempts/{attempt}')->group(function () {
        Route::get('/items', [\App\Http\Controllers\Api\ActivityController::class, 'getItems']);
        Route::post('/submit', [\App\Http\Controllers\Api\ActivityController::class, 'submitResult']);
        Route::post('/complete', [\App\Http\Controllers\Api\ActivityController::class, 'complete']);
    });
});
