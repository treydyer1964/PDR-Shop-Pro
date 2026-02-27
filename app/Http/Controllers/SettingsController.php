<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('settings.shop');
    }

    public function shop(): View
    {
        return view('settings.shop');
    }

    public function locations(): View
    {
        return view('settings.locations');
    }

    public function expenseCategories(): View
    {
        return view('settings.expense-categories');
    }

    public function appointmentTypes(): View
    {
        return view('settings.appointment-types');
    }

    public function vehicleColors(): View
    {
        return view('settings.vehicle-colors');
    }

    public function insuranceCompanies(): View
    {
        return view('settings.insurance-companies');
    }
}
