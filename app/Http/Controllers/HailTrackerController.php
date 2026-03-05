<?php

namespace App\Http\Controllers;

class HailTrackerController extends Controller
{
    public function index()
    {
        return view('hail-tracker.index');
    }
}
