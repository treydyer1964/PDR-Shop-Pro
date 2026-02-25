<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class RentalController extends Controller
{
    public function index(): View
    {
        return view('rentals.index');
    }
}
