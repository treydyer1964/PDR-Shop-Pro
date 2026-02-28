<?php

namespace App\Http\Controllers;

use App\Enums\WorkOrderStatus;
use App\Models\WorkOrder;
use App\Models\WorkOrderCommission;

class DashboardController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $startOfMonth = now()->startOfMonth();

        $openJobs = WorkOrder::where('tenant_id', $tenantId)
            ->where('kicked', false)
            ->where('status', '!=', WorkOrderStatus::Delivered->value)
            ->count();

        $jobsThisMonth = WorkOrder::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $startOfMonth)
            ->where('kicked', false)
            ->count();

        // Revenue = sum of invoice_total for WOs delivered this month (via status log)
        $revenueMtd = WorkOrder::where('tenant_id', $tenantId)
            ->whereNotNull('invoice_total')
            ->whereIn('id', function ($q) use ($tenantId, $startOfMonth) {
                $q->select('work_order_id')
                    ->from('work_order_status_logs')
                    ->where('tenant_id', $tenantId)
                    ->where('status', WorkOrderStatus::Delivered->value)
                    ->where('entered_at', '>=', $startOfMonth);
            })
            ->sum('invoice_total');

        $unpaidCommissions = WorkOrderCommission::where('tenant_id', $tenantId)
            ->where('is_paid', false)
            ->sum('amount');

        return view('dashboard', compact(
            'openJobs',
            'jobsThisMonth',
            'revenueMtd',
            'unpaidCommissions',
        ));
    }
}
