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
        abort_unless(auth()->user()->canCreateWorkOrders(), 403);
        return view('work-orders.create');
    }

    public function edit(WorkOrder $workOrder)
    {
        abort_unless($workOrder->tenant_id === auth()->user()->tenant_id, 403);
        // Field staff cannot edit work orders
        abort_if(auth()->user()->isFieldStaff(), 403);
        return view('work-orders.edit', compact('workOrder'));
    }

    public function show(WorkOrder $workOrder)
    {
        abort_unless($workOrder->tenant_id === auth()->user()->tenant_id, 403);

        // Field staff can only view work orders they are assigned to
        if (auth()->user()->isFieldStaff()) {
            $assigned = $workOrder->assignments()->where('user_id', auth()->id())->exists();
            abort_unless($assigned, 403);
        }

        $workOrder->load([
            'customer',
            'vehicle',
            'insuranceCompany',
            'stormEvent',
            'statusLogs.user',
            'events.user',
            'assignments.user',
        ]);

        return view('work-orders.show', compact('workOrder'));
    }

    public function destroy(WorkOrder $workOrder)
    {
        abort_unless($workOrder->tenant_id === auth()->user()->tenant_id, 403);
        abort_unless(auth()->user()->role === 'owner', 403);

        $workOrder->delete(); // cascades to all child tables

        return redirect()->route('work-orders.index')
            ->with('success', 'Work order ' . $workOrder->ro_number . ' deleted.');
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

    public function rentalAgreementPdf(WorkOrder $workOrder): \Illuminate\Http\Response
    {
        abort_unless($workOrder->tenant_id === auth()->user()->tenant_id, 403);

        $rental = WorkOrderRental::where('work_order_id', $workOrder->id)
            ->with(['vehicle', 'segments'])
            ->first();

        $rentalVehicle = $rental?->vehicle;

        // Use the first open segment for pre-filling; fall back to most recent
        $segment = null;
        if ($rental) {
            $segment = $rental->segments->firstWhere('end_date', null)
                ?? $rental->segments->sortByDesc('start_date')->first();
        }

        $workOrder->load(['customer', 'vehicle', 'tenant']);
        $tenant = $workOrder->tenant;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'work-orders.rental-agreement-pdf',
            compact('workOrder', 'rental', 'rentalVehicle', 'segment', 'tenant')
        )->setPaper('letter', 'portrait');

        $filename = 'rental-agreement-' . $workOrder->ro_number . '.pdf';
        return $pdf->stream($filename);
    }
}
