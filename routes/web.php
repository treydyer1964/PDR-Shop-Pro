<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\StormEventController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\DashboardController;
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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // VIN extraction via OpenAI Vision
    Route::post('/vin/extract', [VehicleController::class, 'extractVin'])->name('vin.extract');

    // ── Work Orders ───────────────────────────────────────────────────────────
    // Index and show are accessible to all auth users (field staff scoped in Livewire/controller)
    Route::get('/work-orders',                     [WorkOrderController::class, 'index'])->name('work-orders.index');
    Route::get('/work-orders/{workOrder}',          [WorkOrderController::class, 'show'])->name('work-orders.show');
    Route::get('/work-orders/{workOrder}/edit',     [WorkOrderController::class, 'edit'])->name('work-orders.edit');

    // Create restricted: Owner, Bookkeeper, Sales Manager, Sales Advisor
    Route::get('/work-orders/create', [WorkOrderController::class, 'create'])
        ->middleware('role:owner,bookkeeper,sales_manager,sales_advisor')
        ->name('work-orders.create');

    // PDF routes: same access as show (controller checks assignment for field staff)
    Route::get('/work-orders/{workOrder}/invoice/pdf',           [WorkOrderController::class, 'invoicePdf'])->name('work-orders.invoice-pdf');
    Route::get('/work-orders/{workOrder}/rental-invoice/pdf',    [WorkOrderController::class, 'rentalInvoicePdf'])->name('work-orders.rental-invoice-pdf');
    Route::get('/work-orders/{workOrder}/rental-agreement',      [WorkOrderController::class, 'rentalAgreementPdf'])->name('work-orders.rental-agreement-pdf');

    // ── Staff ─────────────────────────────────────────────────────────────────
    Route::resource('staff', StaffController::class)
        ->only(['index', 'create', 'show', 'edit', 'destroy'])
        ->parameters(['staff' => 'staff']);

    // ── Customers & Vehicles ──────────────────────────────────────────────────
    Route::resource('customers', CustomerController::class)
        ->only(['index', 'create', 'show', 'edit']);

    Route::get('customers/{customer}/vehicles/create', [VehicleController::class, 'create'])
        ->name('customers.vehicles.create');
    Route::get('customers/{customer}/vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])
        ->name('customers.vehicles.edit');

    // ── Operations (all auth users) ───────────────────────────────────────────
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/rentals',      [RentalController::class, 'index'])->name('rentals.index');

    // ── Storm Events: Owner, Bookkeeper, Sales Manager ────────────────────────
    Route::middleware('role:owner,bookkeeper,sales_manager')->group(function () {
        Route::get('/storm-events',          [StormEventController::class, 'index'])->name('storm-events.index');
        Route::get('/storm-events/{stormEvent}', [StormEventController::class, 'show'])->name('storm-events.show');
    });

    // ── Analytics: Owner, Bookkeeper, Sales Manager ───────────────────────────
    Route::middleware('role:owner,bookkeeper,sales_manager')->group(function () {
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    });

    // ── Commissions: all authenticated users (scoped to self for field staff) ──
    Route::get('/commissions', [CommissionController::class, 'index'])->name('commissions.index');

    // ── Payroll: Owner and Bookkeeper only ────────────────────────────────────
    Route::middleware('role:owner,bookkeeper')->group(function () {
        Route::get('/payroll',                           [PayRunController::class, 'index'])->name('payroll.index');
        Route::get('/payroll/create',                    [PayRunController::class, 'create'])->name('payroll.create');
        Route::get('/payroll/{payRun}/staff/{user}/pdf', [PayRunController::class, 'staffPdf'])->name('payroll.staff-pdf');
        Route::get('/payroll/{payRun}',                  [PayRunController::class, 'show'])->name('payroll.show');
    });

    // ── Settings: Owner only ──────────────────────────────────────────────────
    Route::middleware('role:owner')->group(function () {
        Route::get('/settings',                          [SettingsController::class, 'index'])->name('settings.index');
        Route::get('/settings/shop',                     [SettingsController::class, 'shop'])->name('settings.shop');
        Route::get('/settings/locations',                [SettingsController::class, 'locations'])->name('settings.locations');
        Route::get('/settings/expense-categories',       [SettingsController::class, 'expenseCategories'])->name('settings.expense-categories');
        Route::get('/settings/appointment-types',        [SettingsController::class, 'appointmentTypes'])->name('settings.appointment-types');
        Route::get('/settings/vehicle-colors',           [SettingsController::class, 'vehicleColors'])->name('settings.vehicle-colors');
        Route::get('/settings/insurance-companies',      [SettingsController::class, 'insuranceCompanies'])->name('settings.insurance-companies');
    });

    // ── Profile ───────────────────────────────────────────────────────────────
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
