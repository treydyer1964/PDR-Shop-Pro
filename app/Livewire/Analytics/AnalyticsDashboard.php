<?php

namespace App\Livewire\Analytics;

use App\Enums\Role;
use App\Enums\WorkOrderJobType;
use App\Enums\WorkOrderStatus;
use App\Models\RentalVehicle;
use App\Models\WorkOrder;
use App\Models\WorkOrderCommission;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AnalyticsDashboard extends Component
{
    use WithPagination;

    #[Url] public string $preset         = 'this_month';
    #[Url] public string $dateFrom       = '';
    #[Url] public string $dateTo         = '';
    #[Url] public string $filterLocation = '';

    // Work Orders tab sub-filters
    #[Url] public string $woFilterStatus   = '';
    #[Url] public string $woFilterJobType  = '';
    #[Url] public string $woFilterAdvisor  = '';

    public function updatedPreset(): void
    {
        if ($this->preset === 'custom') {
            $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
            $this->dateTo   = now()->endOfMonth()->format('Y-m-d');
        }
        $this->resetPage();
    }

    public function updatedFilterLocation(): void   { $this->resetPage(); }
    public function updatedDateFrom(): void          { $this->resetPage(); }
    public function updatedDateTo(): void            { $this->resetPage(); }
    public function updatedWoFilterStatus(): void    { $this->resetPage(); }
    public function updatedWoFilterJobType(): void   { $this->resetPage(); }
    public function updatedWoFilterAdvisor(): void   { $this->resetPage(); }

    // ── Date range helper ────────────────────────────────────────────────────

    private function dateRange(): array
    {
        return match ($this->preset) {
            'this_week'  => [now()->startOfWeek(),              now()->endOfWeek()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'ytd'        => [now()->startOfYear(),              now()],
            'custom'     => [
                $this->dateFrom ? Carbon::parse($this->dateFrom)->startOfDay() : now()->startOfMonth(),
                $this->dateTo   ? Carbon::parse($this->dateTo)->endOfDay()     : now()->endOfMonth(),
            ],
            default      => [now()->startOfMonth(), now()->endOfMonth()], // this_month
        };
    }

    private function tenantId(): int
    {
        return auth()->user()->tenant_id;
    }

    // ── Base WO query (date range + location, not kicked) ───────────────────

    private function baseWoQuery()
    {
        [$from, $to] = $this->dateRange();

        return WorkOrder::where('tenant_id', $this->tenantId())
            ->whereBetween('created_at', [$from, $to])
            ->where('kicked', false)
            ->when($this->filterLocation, fn ($q) => $q->where('location_id', $this->filterLocation));
    }

    // ── KPIs (live — no date filter) ─────────────────────────────────────────

    #[Computed]
    public function kpis(): array
    {
        $tid = $this->tenantId();

        return [
            'open_jobs' => WorkOrder::where('tenant_id', $tid)
                ->where('kicked', false)
                ->where('status', '!=', WorkOrderStatus::Delivered->value)
                ->count(),

            'to_be_acquired' => WorkOrder::where('tenant_id', $tid)
                ->where('status', WorkOrderStatus::ToBeAcquired->value)
                ->count(),

            'on_hold' => WorkOrder::where('tenant_id', $tid)
                ->where('on_hold', true)
                ->count(),

            'unpaid_commissions' => (float) WorkOrderCommission::where('tenant_id', $tid)
                ->where('is_paid', false)
                ->sum('amount'),
        ];
    }

    // ── Profit Overview ──────────────────────────────────────────────────────

    #[Computed]
    public function profitOverview(): array
    {
        $wos = $this->baseWoQuery()
            ->withSum('expenses', 'amount')
            ->withSum('payments', 'amount')
            ->get();

        $gross      = (float) $wos->whereNotNull('invoice_total')->sum('invoice_total');
        $expenses   = (float) $wos->sum('expenses_sum_amount');
        $net        = $gross - $expenses;
        $paid       = (float) $wos->sum('payments_sum_amount');
        $outstanding = $gross - $paid;
        $woCount    = $wos->count();

        $byType = [];
        foreach (WorkOrderJobType::cases() as $type) {
            $typeWos   = $wos->where('job_type', $type);
            $typeGross = (float) $typeWos->whereNotNull('invoice_total')->sum('invoice_total');
            $typeExp   = (float) $typeWos->sum('expenses_sum_amount');
            $byType[$type->value] = [
                'label'    => $type->label(),
                'count'    => $typeWos->count(),
                'gross'    => $typeGross,
                'expenses' => $typeExp,
                'net'      => $typeGross - $typeExp,
            ];
        }

        return compact('gross', 'expenses', 'net', 'paid', 'outstanding', 'woCount', 'byType');
    }

    // ── Cycle Time ──────────────────────────────────────────────────────────

    #[Computed]
    public function cycleTime(): array
    {
        $tid         = $this->tenantId();
        [$from, $to] = $this->dateRange();

        // WO IDs in range (acquired status log entered in period)
        $woIdsQuery = DB::table('work_order_status_logs as sl')
            ->join('work_orders as wo', 'wo.id', '=', 'sl.work_order_id')
            ->where('sl.tenant_id', $tid)
            ->where('sl.status', WorkOrderStatus::Acquired->value)
            ->whereBetween('sl.entered_at', [$from, $to])
            ->where('wo.kicked', false)
            ->when($this->filterLocation, fn ($q) => $q->where('wo.location_id', $this->filterLocation));

        $woIds = $woIdsQuery->pluck('sl.work_order_id');

        if ($woIds->isEmpty()) {
            return ['avg_days' => null, 'delivered_count' => 0, 'stages' => [], 'avg_insurance_wait_days' => null];
        }

        // Avg total days: acquired → delivered
        $avgTotal = DB::table('work_order_status_logs as acq')
            ->join('work_order_status_logs as del', 'del.work_order_id', '=', 'acq.work_order_id')
            ->whereIn('acq.work_order_id', $woIds)
            ->where('acq.status', WorkOrderStatus::Acquired->value)
            ->where('del.status', WorkOrderStatus::Delivered->value)
            ->selectRaw('AVG(TIMESTAMPDIFF(DAY, acq.entered_at, del.entered_at)) as avg_days, COUNT(*) as cnt')
            ->first();

        // Per-stage avg (only closed stages)
        $stageRows = DB::table('work_order_status_logs')
            ->whereIn('work_order_id', $woIds)
            ->where('tenant_id', $tid)
            ->whereNotNull('exited_at')
            ->selectRaw('status, AVG(TIMESTAMPDIFF(HOUR, entered_at, exited_at)) as avg_hours, COUNT(*) as stage_count')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        // Build ordered stage array
        $stages = collect(WorkOrderStatus::cases())->map(function ($s) use ($stageRows) {
            $row      = $stageRows->get($s->value);
            $avgHours = $row ? round((float) $row->avg_hours, 1) : null;
            $avgDays  = $avgHours !== null ? round($avgHours / 24, 1) : null;
            return [
                'status'    => $s,
                'avg_hours' => $avgHours,
                'avg_days'  => $avgDays,
                'count'     => $row?->stage_count ?? 0,
            ];
        })->filter(fn ($s) => $s['count'] > 0)->values();

        // Max avg_hours for relative bar widths
        $maxHours = $stages->max('avg_hours') ?: 1;

        $stages = $stages->map(fn ($s) => array_merge($s, [
            'bar_pct' => $s['avg_hours'] !== null ? round($s['avg_hours'] / $maxHours * 100) : 0,
        ]))->values();

        // Insurance approval wait avg
        $insuranceWait = DB::table('work_order_status_logs')
            ->whereIn('work_order_id', $woIds)
            ->where('status', WorkOrderStatus::WaitingOnInsurance->value)
            ->whereNotNull('exited_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, entered_at, exited_at)) as avg_hours')
            ->value('avg_hours');

        $avgInsuranceWaitDays = $insuranceWait ? round($insuranceWait / 24, 1) : null;

        return [
            'avg_days'                 => $avgTotal?->avg_days !== null ? round((float) $avgTotal->avg_days, 1) : null,
            'delivered_count'          => (int) ($avgTotal?->cnt ?? 0),
            'stages'                   => $stages,
            'avg_insurance_wait_days'  => $avgInsuranceWaitDays,
            'wo_count'                 => $woIds->count(),
        ];
    }

    // ── Insurance Companies ──────────────────────────────────────────────────

    #[Computed]
    public function insuranceCompanies(): \Illuminate\Support\Collection
    {
        $wos = $this->baseWoQuery()
            ->whereNotNull('insurance_company_id')
            ->with(['insuranceCompany', 'expenses', 'statusLogs'])
            ->get();

        if ($wos->isEmpty()) {
            return collect();
        }

        return $wos->groupBy('insurance_company_id')
            ->map(function ($companyWos) {
                $waits = $companyWos->map(fn ($wo) => $wo->daysWaitingOnInsurance())
                    ->filter(fn ($d) => $d !== null);

                $deliveredWos = $companyWos->filter(fn ($wo) => $wo->isDelivered());
                $cycles       = $deliveredWos->map(fn ($wo) => $wo->daysInShop());

                $withInvoice  = $companyWos->filter(fn ($wo) => $wo->invoice_total !== null);

                return [
                    'company'                  => $companyWos->first()->insuranceCompany,
                    'count'                    => $companyWos->count(),
                    'avg_invoice'              => $withInvoice->count() ? round($withInvoice->avg('invoice_total'), 2) : null,
                    'avg_net'                  => $withInvoice->count() ? round($withInvoice->avg(fn ($wo) => $wo->netTotal()), 2) : null,
                    'avg_cycle_days'           => $cycles->count() ? round($cycles->avg(), 1) : null,
                    'avg_approval_wait_days'   => $waits->count() ? round($waits->avg(), 1) : null,
                ];
            })
            ->sortByDesc('count')
            ->values();
    }

    // ── Staff Performance ────────────────────────────────────────────────────

    #[Computed]
    public function staffPerformance(): array
    {
        $commissions = WorkOrderCommission::where('tenant_id', $this->tenantId())
            ->whereHas('workOrder', fn ($q) => $this->applyDateAndLocationToWoQuery($q))
            ->with(['user', 'workOrder.expenses'])
            ->get();

        $byUserRole = $commissions
            ->groupBy(fn ($c) => $c->user_id . '_' . $c->role->value)
            ->map(function ($items) {
                $user    = $items->first()->user;
                $role    = $items->first()->role;
                $wos     = $items->pluck('workOrder')->unique('id');
                $withInv = $wos->filter(fn ($wo) => $wo->invoice_total !== null);

                return [
                    'user'             => $user,
                    'role'             => $role,
                    'job_count'        => $wos->count(),
                    'total_commission' => round((float) $items->sum('amount'), 2),
                    'avg_commission'   => round((float) $items->avg('amount'), 2),
                    'gross_revenue'    => round((float) $withInv->sum('invoice_total'), 2),
                    'net_revenue'      => round((float) $withInv->sum(fn ($wo) => $wo->netTotal()), 2),
                ];
            })
            ->values();

        $advisors = $byUserRole->filter(fn ($r) => $r['role'] === Role::SALES_ADVISOR)
            ->sortByDesc('total_commission')->values();

        $techs = $byUserRole->filter(fn ($r) => $r['role'] === Role::PDR_TECH)
            ->sortByDesc('total_commission')->values();

        $others = $byUserRole->filter(fn ($r) => !in_array($r['role'], [Role::SALES_ADVISOR, Role::PDR_TECH]))
            ->sortByDesc('total_commission')->values();

        return compact('advisors', 'techs', 'others');
    }

    // ── Rental Utilization ───────────────────────────────────────────────────

    #[Computed]
    public function rentalUtilization(): \Illuminate\Support\Collection
    {
        [$from, $to] = $this->dateRange();
        $periodDays  = max(1, (int) Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1);

        $vehicles = RentalVehicle::where('tenant_id', $this->tenantId())
            ->active()
            ->with(['workOrderRentals.segments'])
            ->orderBy('name')
            ->get();

        return $vehicles->map(function ($vehicle) use ($from, $to, $periodDays) {
            $totalDays   = 0;
            $internalCost = 0.0;
            $insBilled   = 0.0;
            $rentalCount = 0;

            foreach ($vehicle->workOrderRentals as $rental) {
                $daysInPeriod = 0;
                foreach ($rental->segments as $segment) {
                    $segStart = max($segment->start_date, Carbon::parse($from)->toDateString());
                    $rawEnd   = $segment->end_date ?? Carbon::parse($to)->toDateString();
                    $segEnd   = min($rawEnd, Carbon::parse($to)->toDateString());

                    if ($segEnd > $segStart) {
                        $days         = Carbon::parse($segStart)->diffInDays(Carbon::parse($segEnd));
                        $daysInPeriod += $days;
                    }
                }

                if ($daysInPeriod > 0) {
                    $rentalCount++;
                    $totalDays    += $daysInPeriod;
                    $internalCost += $daysInPeriod * (float) $vehicle->internal_daily_cost;

                    if ($rental->has_insurance_coverage && $rental->insurance_daily_rate) {
                        $insBilled += $daysInPeriod * (float) $rental->insurance_daily_rate;
                    }
                }
            }

            return [
                'vehicle'          => $vehicle,
                'rental_count'     => $rentalCount,
                'total_days_out'   => $totalDays,
                'internal_cost'    => round($internalCost, 2),
                'insurance_billed' => round($insBilled, 2),
                'utilization_pct'  => $periodDays > 0 ? min(100, round($totalDays / $periodDays * 100)) : 0,
                'period_days'      => $periodDays,
            ];
        })->sortByDesc('utilization_pct')->values();
    }

    // ── Work Orders (paginated) ──────────────────────────────────────────────

    #[Computed]
    public function workOrders()
    {
        $query = $this->baseWoQuery()
            ->with(['customer', 'vehicle', 'expenses', 'statusLogs',
                    'assignments' => fn ($q) => $q->with('user')])
            ->when($this->woFilterStatus,  fn ($q) => $q->where('status', $this->woFilterStatus))
            ->when($this->woFilterJobType, fn ($q) => $q->where('job_type', $this->woFilterJobType))
            ->when($this->woFilterAdvisor, fn ($q) => $q->whereHas('assignments', fn ($aq) =>
                $aq->where('user_id', $this->woFilterAdvisor)
                   ->where('role', Role::SALES_ADVISOR->value)
            ))
            ->orderByDesc('created_at');

        return $query->paginate(25);
    }

    // ── Supporting computed ──────────────────────────────────────────────────

    #[Computed]
    public function locations(): \Illuminate\Support\Collection
    {
        return \App\Models\Location::where('tenant_id', $this->tenantId())
            ->where('active', true)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function staffList(): \Illuminate\Support\Collection
    {
        return \App\Models\User::where('tenant_id', $this->tenantId())
            ->where('active', true)
            ->orderBy('name')
            ->get();
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function applyDateAndLocationToWoQuery($query)
    {
        [$from, $to] = $this->dateRange();

        return $query
            ->where('tenant_id', $this->tenantId())
            ->whereBetween('created_at', [$from, $to])
            ->where('kicked', false)
            ->when($this->filterLocation, fn ($q) => $q->where('location_id', $this->filterLocation));
    }

    public function render()
    {
        return view('livewire.analytics.analytics-dashboard');
    }
}
