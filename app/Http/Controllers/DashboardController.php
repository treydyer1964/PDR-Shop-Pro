<?php

namespace App\Http\Controllers;

use App\Enums\WorkOrderStatus;
use App\Models\RentalVehicle;
use App\Models\WorkOrder;
use App\Models\WorkOrderCommission;

class DashboardController extends Controller
{
    public function index()
    {
        $user         = auth()->user();
        $tenantId     = $user->tenant_id;
        $startOfMonth = now()->startOfMonth();

        if ($user->isFieldStaff()) {
            // Field staff: scope everything to their assigned work orders
            $openJobs = WorkOrder::where('tenant_id', $tenantId)
                ->whereHas('assignments', fn($a) => $a->where('user_id', $user->id))
                ->where('kicked', false)
                ->where('status', '!=', WorkOrderStatus::Delivered->value)
                ->count();

            $jobsThisMonth = WorkOrder::where('tenant_id', $tenantId)
                ->whereHas('assignments', fn($a) => $a->where('user_id', $user->id))
                ->where('created_at', '>=', $startOfMonth)
                ->where('kicked', false)
                ->count();

            $unpaidCommissions = WorkOrderCommission::where('tenant_id', $tenantId)
                ->where('user_id', $user->id)
                ->where('is_paid', false)
                ->sum('amount');

            $revenueMtd  = null; // not shown for field staff
        } else {
            $openJobs = WorkOrder::where('tenant_id', $tenantId)
                ->where('kicked', false)
                ->where('status', '!=', WorkOrderStatus::Delivered->value)
                ->count();

            $jobsThisMonth = WorkOrder::where('tenant_id', $tenantId)
                ->where('created_at', '>=', $startOfMonth)
                ->where('kicked', false)
                ->count();

            // Revenue = sum of invoice_total for WOs delivered this month
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
        }

        // Fleet service alert (owner/manager only — non-field staff)
        $fleetServiceCount = null;
        if (! $user->isFieldStaff()) {
            $vehicles = RentalVehicle::forTenant($tenantId)->active()->get();
            $fleetServiceCount = $vehicles->filter(
                fn($v) => in_array($v->serviceStatus(), ['overdue', 'due_soon'])
            )->count();
        }

        return view('dashboard', compact(
            'openJobs',
            'jobsThisMonth',
            'revenueMtd',
            'unpaidCommissions',
            'fleetServiceCount',
        ));
    }
}
