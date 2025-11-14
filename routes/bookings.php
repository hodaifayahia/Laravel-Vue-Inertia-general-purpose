<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ProviderProfileController;
use App\Http\Controllers\ProviderScheduleController;
use App\Http\Controllers\ProviderAvailabilityController;
use App\Http\Controllers\SpecializationController;
use Illuminate\Support\Facades\Route;

// Booking routes
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Specializations (Admin only)
    Route::middleware('permission:manage bookings')->group(function () {
        Route::get('/specializations', [SpecializationController::class, 'index'])->name('specializations.index');
        Route::post('/specializations', [SpecializationController::class, 'store'])->name('specializations.store');
        Route::put('/specializations/{specialization}', [SpecializationController::class, 'update'])->name('specializations.update');
        Route::delete('/specializations/{specialization}', [SpecializationController::class, 'destroy'])->name('specializations.destroy');
    });

    // Active specializations (for booking)
    Route::get('/specializations/active', [SpecializationController::class, 'active'])->name('specializations.active');

    // Provider Profile (for users with book-sys permission)
    Route::middleware('permission:book-sys')->group(function () {
        Route::get('/provider/configuration', [ProviderProfileController::class, 'configuration'])->name('provider.configuration');
        Route::post('/provider/profile', [ProviderProfileController::class, 'store'])->name('provider.profile.store');
        
        // Provider Schedule
        Route::get('/provider/schedule', [ProviderScheduleController::class, 'index'])->name('provider.schedule.index');
        Route::post('/provider/schedule/bulk', [ProviderScheduleController::class, 'bulkUpdate'])->name('provider.schedule.bulk');
        
        // Provider Availability (Date-specific configuration)
        Route::get('/provider/availability', [ProviderAvailabilityController::class, 'index'])->name('provider.availability.index');
        Route::post('/provider/availability', [ProviderAvailabilityController::class, 'store'])->name('provider.availability.store');
        Route::post('/provider/availability/bulk', [ProviderAvailabilityController::class, 'bulkStore'])->name('provider.availability.bulk');
        Route::post('/provider/availability/exclude', [ProviderAvailabilityController::class, 'storeExcludedDates'])->name('provider.availability.exclude');
        Route::delete('/provider/availability/{id}', [ProviderAvailabilityController::class, 'destroy'])->name('provider.availability.destroy');
        Route::get('/provider/availability/month', [ProviderAvailabilityController::class, 'getMonthAvailability'])->name('provider.availability.month');
    });

    // Providers list (for booking)
    Route::get('/providers/{specialization}', [ProviderProfileController::class, 'bySpecialization'])->name('providers.by-specialization');
    
    // Provider details page (public)
    Route::get('/providers/{provider}/details', [ProviderProfileController::class, 'details'])->name('providers.details');
    
    // Available time slots
    Route::get('/providers/{provider}/slots', [ProviderScheduleController::class, 'availableSlots'])->name('provider.slots');

    // Appointments
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');
    
    // Booking (for users with can-book permission)
    Route::middleware('permission:can-book')->group(function () {
        Route::get('/book', [AppointmentController::class, 'create'])->name('appointments.create');
        Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
        Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    });

    // Provider actions
    Route::middleware('permission:book-sys')->group(function () {
        Route::post('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.update-status');
    });

    // Admin views
    Route::middleware('permission:manage bookings')->group(function () {
        Route::get('/providers', [ProviderProfileController::class, 'index'])->name('providers.index');
        Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
    });
});
