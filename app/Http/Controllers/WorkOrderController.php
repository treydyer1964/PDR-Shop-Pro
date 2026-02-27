<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\WorkOrderRental;

class WorkOrderController extends Controller
{
    public function index()
    {
        return view('work-orders.index');
    }

    public function create()
    {
        return view('work-orders.create');
    }

    public function edit(WorkOrder $workOrder)
    {
        abort_unless($workOrder->tenant_id === auth()->user()->tenant_id, 403);
        return view('work-orders.edit', compact('workOrder'));
    }

    public function show(WorkOrder $workOrder)
    {
        abort_unless($workOrder->tenant_id === auth()->user()->tenant_id, 403);

        $workOrder->load([
            'customer',
            'vehicle',
            'insuranceCompany',
            'statusLogs.user',
            'events.user',
            'assignments.user',
        ]);

        return view('work-orders.show', compact('workOrder'));
    }

    public function invoicePdf(WorkOrder $workOrder): \Illuminate\Http\Response
    {
        abort_unless($workOrder->tenant_id === auth()->user()->tenant_id, 403);
        $workOrder->load(['customer', 'vehicle', 'payments', 'insuranceCompany', 'tenant']);
        $tenant = $workOrder->tenant;
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('work-orders.invoice-pdf', compact('workOrder', 'tenant'))
            ->setPaper('letter', 'portrait');
        $filename = 'invoice-' . $workOrder->ro_number . '.pdf';
        return $pdf->download($filename);
    }

    public function rentalInvoicePdf(WorkOrder $workOrder): \Illuminate\Http\Response
    {
        abort_unless($workOrder->tenant_id === auth()->user()->tenant_id, 403);
        $rental = WorkOrderRental::where('work_order_id', $workOrder->id)
            ->with(['vehicle', 'segments'])
            ->firstOrFail();
        $workOrder->load(['customer', 'vehicle', 'insuranceCompany', 'tenant']);
        $tenant = $workOrder->tenant;
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('work-orders.rental-invoice-pdf', compact('workOrder', 'rental', 'tenant'))
            ->setPaper('letter', 'portrait');
        $filename = 'rental-invoice-' . $workOrder->ro_number . '.pdf';
        return $pdf->download($filename);
    }
}
