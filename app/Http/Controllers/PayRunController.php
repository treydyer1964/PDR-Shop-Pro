<?php

namespace App\Http\Controllers;

use App\Models\PayRun;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
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

    public function staffPdf(PayRun $payRun, User $user)
    {
        $tenant = auth()->user()->tenant;
        abort_unless($payRun->tenant_id === $tenant->id, 403);
        abort_unless($user->tenant_id === $tenant->id, 403);

        $commissions = $payRun->commissions()
            ->where('user_id', $user->id)
            ->with([
                'workOrder.customer',
                'workOrder.vehicle',
                'workOrder.expenses',
            ])
            ->get()
            ->sortBy('work_order_id');

        abort_if($commissions->isEmpty(), 404);

        $total = $commissions->sum('amount');

        $pdf = Pdf::loadView('payroll.pay-stub-pdf', compact('payRun', 'user', 'tenant', 'commissions', 'total'))
            ->setPaper('letter', 'portrait');

        $filename = sprintf(
            'PayStub-%s-%s.pdf',
            str_replace(' ', '', $user->name),
            now()->format('Ymd')
        );

        return $pdf->download($filename);
    }
}
