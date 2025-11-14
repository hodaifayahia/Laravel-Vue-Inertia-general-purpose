<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProviderController;

// Get providers by city and specialization
Route::get('/providers', [ProviderController::class, 'getProvidersByCityAndSpecialization']);

// Get available dates for a provider
Route::get('/providers/{providerId}/available-dates', [ProviderController::class, 'getAvailableDates']);

// Get available time slots for a specific date
Route::get('/providers/{providerId}/slots', [ProviderController::class, 'getAvailableSlots']);
