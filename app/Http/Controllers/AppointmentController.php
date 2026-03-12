<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(): View
    {
        return view('appointments.index');
    }

    public function calendar(): View
    {
        return view('appointments.calendar');
    }
}
