<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;

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
}
