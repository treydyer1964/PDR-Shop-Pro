<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

    // Work Orders
    Route::resource('work-orders', WorkOrderController::class)
        ->only(['index', 'create', 'show']);

    // Customers
    Route::resource('customers', CustomerController::class)
        ->only(['index', 'create', 'show', 'edit']);

    // Vehicles (nested under customers)
    Route::get('customers/{customer}/vehicles/create', [VehicleController::class, 'create'])
        ->name('customers.vehicles.create');
    Route::get('customers/{customer}/vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])
        ->name('customers.vehicles.edit');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
