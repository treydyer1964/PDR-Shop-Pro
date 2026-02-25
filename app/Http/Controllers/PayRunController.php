<?php

namespace App\Http\Controllers;

use App\Models\PayRun;
use Illuminate\Http\Request;

class PayRunController extends Controller
{
    public function index()
    {
        return view('payroll.index');
    }

    public function create()
    {
        return view('payroll.create');
    }

    public function show(PayRun $payRun)
    {
        abort_unless($payRun->tenant_id === auth()->user()->tenant_id, 403);
        return view('payroll.show', compact('payRun'));
    }
}
