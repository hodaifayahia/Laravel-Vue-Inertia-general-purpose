<?php

use App\Http\Controllers\ChildrenController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('children', ChildrenController::class);
});
