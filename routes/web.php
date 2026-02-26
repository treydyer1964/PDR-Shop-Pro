<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\PhotoShareController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PayRunController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Public photo share gallery (signed URL, no auth required)
Route::get('/share/{workOrder}', [PhotoShareController::class, 'show'])->name('photos.share');

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

    // VIN extraction via OpenAI Vision
    Route::post('/vin/extract', [VehicleController::class, 'extractVin'])->name('vin.extract');

    // Work Orders
    Route::resource('work-orders', WorkOrderController::class)
        ->only(['index', 'create', 'show', 'edit']);

    // Staff (uses {staff} param to avoid collision with User model binding)
    Route::resource('staff', StaffController::class)
        ->only(['index', 'create', 'show', 'edit', 'destroy'])
        ->parameters(['staff' => 'staff']);

    // Customers
    Route::resource('customers', CustomerController::class)
        ->only(['index', 'create', 'show', 'edit']);

    // Vehicles (nested under customers)
    Route::get('customers/{customer}/vehicles/create', [VehicleController::class, 'create'])
        ->name('customers.vehicles.create');
    Route::get('customers/{customer}/vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])
        ->name('customers.vehicles.edit');

    // Payroll & Commissions
    Route::get('/payroll', [PayRunController::class, 'index'])->name('payroll.index');
    Route::get('/payroll/create', [PayRunController::class, 'create'])->name('payroll.create');
    Route::get('/payroll/{payRun}', [PayRunController::class, 'show'])->name('payroll.show');
    Route::get('/commissions', [CommissionController::class, 'index'])->name('commissions.index');

    // Appointments
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');

    // Rental Fleet
    Route::get('/rentals', [RentalController::class, 'index'])->name('rentals.index');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('/settings/shop', [SettingsController::class, 'shop'])->name('settings.shop');
    Route::get('/settings/locations', [SettingsController::class, 'locations'])->name('settings.locations');
    Route::get('/settings/expense-categories', [SettingsController::class, 'expenseCategories'])->name('settings.expense-categories');
    Route::get('/settings/appointment-types', [SettingsController::class, 'appointmentTypes'])->name('settings.appointment-types');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
