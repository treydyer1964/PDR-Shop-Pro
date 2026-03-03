<?php

namespace App\Http\Controllers;

use App\Enums\WorkOrderJobType;
use App\Enums\WorkOrderStatus;
use App\Models\StormEvent;

class StormEventController extends Controller
{
    public function index()
    {
        return view('storm-events.index');
    }

    public function show(StormEvent $stormEvent)
    {
        abort_unless($stormEvent->tenant_id === auth()->user()->tenant_id, 403);

        $workOrders = $stormEvent->workOrders()
            ->with(['customer', 'vehicle', 'insuranceCompany', 'expenses'])
            ->get();

        $totalCars    = $workOrders->count();
        $totalRevenue = $workOrders->sum('invoice_total');
        $avgPerCar    = $totalCars > 0 ? $totalRevenue / $totalCars : 0;
        $totalExpenses = $workOrders->sum(fn($wo) => $wo->totalExpenses());
        $totalNet      = (float) $totalRevenue - $totalExpenses;

        $byStatus = $workOrders
            ->filter(fn($wo) => ! $wo->kicked)
            ->groupBy('status')
            ->map(fn($group, $status) => [
                'status' => WorkOrderStatus::from($status),
                'count'  => $group->count(),
            ])
            ->sortBy(fn($item) => $item['status']->position())
            ->values();

        $byInsurance = $workOrders
            ->whereNotNull('insurance_company_id')
            ->groupBy('insurance_company_id')
            ->map(fn($group) => [
                'company' => $group->first()->insuranceCompany?->name ?? 'Unknown',
                'count'   => $group->count(),
                'revenue' => (float) $group->sum('invoice_total'),
            ])
            ->sortByDesc('revenue')
            ->values();

        $byJobType = $workOrders
            ->groupBy('job_type')
            ->map(fn($group, $type) => [
                'label'   => WorkOrderJobType::from($type)->label(),
                'count'   => $group->count(),
                'revenue' => (float) $group->sum('invoice_total'),
            ])
            ->values();

        return view('storm-events.show', compact(
            'stormEvent', 'workOrders',
            'totalCars', 'totalRevenue', 'avgPerCar',
            'byStatus', 'totalExpenses', 'totalNet',
            'byInsurance', 'byJobType'
        ));
    }
}
