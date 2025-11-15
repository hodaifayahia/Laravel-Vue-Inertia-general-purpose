<?php

use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\RoleManagementController;
use App\Http\Controllers\PermissionManagementController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Public dysgraphia platform pages
Route::get('/doctors', function () {
    return Inertia::render('Doctors/Index');
})->name('doctors.index');

Route::get('/doctors/{id}', function ($id) {
    return Inertia::render('Doctors/Show', ['id' => $id]);
})->name('doctors.show');

Route::get('/map', function () {
    return Inertia::render('Map');
})->name('map');

Route::get('/about', function () {
    return Inertia::render('About');
})->name('about');

Route::get('/faq', function () {
    return Inertia::render('FAQ');
})->name('faq');

Route::get('/resources', function () {
    return Inertia::render('Resources');
})->name('resources');

Route::get('/contact', function () {
    return Inertia::render('Contact');
})->name('contact');

// Free Dysgraphia Assessment - accessible without authentication
Route::get('/assessment', function () {
    return Inertia::render('Assessment');
})->name('assessment');

// Public Activity Games - accessible without authentication
Route::get('/activities', function () {
    return Inertia::render('Activities/Index');
})->name('activities.index');

Route::get('/activities/{activity}/play', function ($activity) {
    return Inertia::render('Activities/Game', ['activityId' => $activity]);
})->name('activities.play');

// Public Locations (provinces / cities) - accessible without authentication
Route::get('locations', [\App\Http\Controllers\LocationController::class, 'index'])
    ->name('locations.index');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // User management routes with permission checks
    Route::get('users', [UserManagementController::class, 'index'])
        ->middleware('permission:view users')
        ->name('users.index');
    Route::get('users/create', [UserManagementController::class, 'create'])
        ->middleware('permission:create users')
        ->name('users.create');
    Route::post('users', [UserManagementController::class, 'store'])
        ->middleware('permission:create users')
        ->name('users.store');
    Route::get('users/{user}', [UserManagementController::class, 'show'])
        ->middleware('permission:view users')
        ->name('users.show');
    Route::get('users/{user}/edit', [UserManagementController::class, 'edit'])
        ->middleware('permission:edit users')
        ->name('users.edit');
    Route::put('users/{user}', [UserManagementController::class, 'update'])
        ->middleware('permission:edit users')
        ->name('users.update');
    Route::delete('users/{user}', [UserManagementController::class, 'destroy'])
        ->middleware('permission:delete users')
        ->name('users.destroy');
    
    // Role management routes with permission checks
    Route::get('roles', [RoleManagementController::class, 'index'])
        ->middleware('permission:view roles')
        ->name('roles.index');
    Route::post('roles', [RoleManagementController::class, 'store'])
        ->middleware('permission:create roles')
        ->name('roles.store');
    Route::get('roles/{role}', [RoleManagementController::class, 'show'])
        ->middleware('permission:view roles')
        ->name('roles.show');
    Route::put('roles/{role}', [RoleManagementController::class, 'update'])
        ->middleware('permission:edit roles')
        ->name('roles.update');
    Route::delete('roles/{role}', [RoleManagementController::class, 'destroy'])
        ->middleware('permission:delete roles')
        ->name('roles.destroy');
    Route::post('users/{user}/assign-roles', [RoleManagementController::class, 'assignRolesToUser'])
        ->middleware('permission:assign roles')
        ->name('users.assign-roles');
    
    // Permission management routes with permission checks
    Route::get('permissions', [PermissionManagementController::class, 'index'])
        ->middleware('permission:view permissions')
        ->name('permissions.index');
    Route::post('permissions', [PermissionManagementController::class, 'store'])
        ->middleware('permission:view permissions')
        ->name('permissions.store');
    Route::get('permissions/{permission}', [PermissionManagementController::class, 'show'])
        ->middleware('permission:view permissions')
        ->name('permissions.show');
    Route::put('permissions/{permission}', [PermissionManagementController::class, 'update'])
        ->middleware('permission:view permissions')
        ->name('permissions.update');
    Route::delete('permissions/{permission}', [PermissionManagementController::class, 'destroy'])
        ->middleware('permission:view permissions')
        ->name('permissions.destroy');
    Route::post('users/{user}/assign-permissions', [PermissionManagementController::class, 'assignPermissionsToUser'])
        ->middleware('permission:assign permissions')
        ->name('users.assign-permissions');

    // Locations (provinces / cities) - Admin only
    Route::post('locations/provinces', [\App\Http\Controllers\LocationController::class, 'storeProvince'])
        ->name('locations.provinces.store');
    Route::post('locations/cities', [\App\Http\Controllers\LocationController::class, 'storeCity'])
        ->name('locations.cities.store');

    Route::delete('locations/provinces/{province}', [\App\Http\Controllers\LocationController::class, 'destroyProvince'])
        ->name('locations.provinces.destroy');
    Route::delete('locations/cities/{city}', [\App\Http\Controllers\LocationController::class, 'destroyCity'])
        ->name('locations.cities.destroy');

    // Admin Activity Management
    Route::prefix('admin/activities')->name('admin.activities.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ActivityAdminController::class, 'index'])
            ->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\ActivityAdminController::class, 'create'])
            ->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\ActivityAdminController::class, 'store'])
            ->name('store');
        Route::get('/{activity}/edit', [\App\Http\Controllers\Admin\ActivityAdminController::class, 'edit'])
            ->name('edit');
        Route::put('/{activity}', [\App\Http\Controllers\Admin\ActivityAdminController::class, 'update'])
            ->name('update');
        Route::delete('/{activity}', [\App\Http\Controllers\Admin\ActivityAdminController::class, 'destroy'])
            ->name('destroy');

        // Activity Items
        Route::post('/{activity}/items', [\App\Http\Controllers\Admin\ActivityAdminController::class, 'storeItem'])
            ->name('items.store');
        Route::put('/items/{item}', [\App\Http\Controllers\Admin\ActivityAdminController::class, 'updateItem'])
            ->name('items.update');
        Route::delete('/items/{item}', [\App\Http\Controllers\Admin\ActivityAdminController::class, 'destroyItem'])
            ->name('items.destroy');

        // Attempts/Results
        Route::get('/attempts', [\App\Http\Controllers\Admin\ActivityAdminController::class, 'attempts'])
            ->name('attempts');
        Route::get('/attempts/{attempt}', [\App\Http\Controllers\Admin\ActivityAdminController::class, 'showAttempt'])
            ->name('attempts.show');
        Route::put('/attempts/{attempt}/notes', [\App\Http\Controllers\Admin\ActivityAdminController::class, 'updateAttemptNotes'])
            ->name('attempts.update-notes');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/chat.php';
require __DIR__.'/bookings.php';
