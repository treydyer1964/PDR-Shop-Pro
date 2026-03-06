<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class EstimateController extends Controller
{
    public function import(): View
    {
        return view('estimates.import');
    }
}
