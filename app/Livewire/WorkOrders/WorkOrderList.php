<?php

namespace App\Livewire\WorkOrders;

use App\Enums\WorkOrderJobType;
use App\Enums\WorkOrderStatus;
use App\Models\StormEvent;
use App\Models\WorkOrder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class WorkOrderList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'status')]
    public string $filterStatus = '';

    #[Url(as: 'type')]
    public string $filterType = '';

    #[Url(as: 'storm')]
    public string $filterStorm = '';

    #[Url(as: 'view')]
    public string $filterView = 'in_shop';

    public bool $showKicked = false;

    public function updatedSearch(): void        { $this->resetPage(); }
    public function updatedFilterStatus(): void  { $this->resetPage(); }
    public function updatedFilterType(): void    { $this->resetPage(); }
    public function updatedFilterStorm(): void   { $this->resetPage(); }
    public function updatedFilterView(): void    { $this->resetPage(); }

    #[Computed]
    public function workOrders()
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;

        return WorkOrder::forTenant($tenantId)
            ->with(['customer', 'vehicle', 'insuranceCompany'])
            // Field staff only see work orders they are assigned to
            ->when(! $user->canSeeAllWorkOrders(), fn($q) =>
                $q->whereHas('assignments', fn($a) => $a->where('user_id', $user->id))
            )
            ->when(! $this->showKicked, fn($q) => $q->where('kicked', false))
            // Smart view filters
            ->when($this->filterView === 'in_shop', fn($q) =>
                $q->whereNotIn('status', [
                    WorkOrderStatus::ToBeAcquired->value,
                    WorkOrderStatus::Delivered->value,
                ])->where('kicked', false)
            )
            ->when($this->filterView === 'to_be_acquired', fn($q) =>
                $q->where('status', WorkOrderStatus::ToBeAcquired->value)
            )
            ->when($this->filterView === 'balance_due', fn($q) =>
                $q->whereNotNull('invoice_total')
                  ->whereRaw('invoice_total > (SELECT COALESCE(SUM(amount), 0) FROM work_order_payments WHERE work_order_payments.work_order_id = work_orders.id)')
            )
            ->when($this->filterView === 'unpaid_commissions', fn($q) =>
                $q->whereHas('commissions', fn($c) => $c->where('is_paid', false))
            )
            ->when($this->filterView === 'unbilled_rentals', fn($q) =>
                $q->where('status', WorkOrderStatus::Delivered->value)
                  ->where('has_rental_coverage', true)
                  ->whereHas('workOrderRental', fn($r) => $r->whereDoesntHave('reimbursement'))
            )
            ->when($this->filterView === 'unpaid_rental', fn($q) =>
                $q->whereHas('workOrderRental.reimbursement', fn($r) =>
                    $r->where(fn($inner) =>
                        $inner->whereNull('insurance_amount_received')
                              ->orWhere('insurance_amount_received', 0)
                    )
                )
            )
            // Standard filters (stack on top of view filter)
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType,   fn($q) => $q->where('job_type', $this->filterType))
            ->when($this->filterStorm,  fn($q) => $q->where('storm_event_id', $this->filterStorm))
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('ro_number',    'like', "%{$this->search}%")
                      ->orWhere('claim_number', 'like', "%{$this->search}%")
                      ->orWhereHas('customer', fn($q) =>
                          $q->where('first_name', 'like', "%{$this->search}%")
                            ->orWhere('last_name',  'like', "%{$this->search}%")
                      )
                      ->orWhereHas('vehicle', fn($q) =>
                          $q->where('vin',   'like', "%{$this->search}%")
                            ->orWhere('make', 'like', "%{$this->search}%")
                            ->orWhere('model','like', "%{$this->search}%")
                      );
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20);
    }

    #[Computed]
    public function statuses(): array
    {
        return WorkOrderStatus::cases();
    }

    #[Computed]
    public function jobTypes(): array
    {
        return WorkOrderJobType::cases();
    }

    #[Computed]
    public function stormEvents()
    {
        return StormEvent::forTenant(auth()->user()->tenant_id)
            ->orderByDesc('event_date')
            ->get();
    }

    public function render()
    {
        return view('livewire.work-orders.work-order-list');
    }
}
