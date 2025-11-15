<?php

use App\Http\Controllers\ChildrenController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    // API endpoint for getting authenticated user's children
    Route::get('api/children', [ChildrenController::class, 'apiIndex']);
    
    // Resource routes for children management
    Route::resource('children', ChildrenController::class);
});
