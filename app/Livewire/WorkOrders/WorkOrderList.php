<?php

namespace App\Livewire\WorkOrders;

use App\Enums\WorkOrderJobType;
use App\Enums\WorkOrderStatus;
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

    public bool $showKicked = false;

    public function updatedSearch(): void    { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }
    public function updatedFilterType(): void   { $this->resetPage(); }

    #[Computed]
    public function workOrders()
    {
        $tenantId = auth()->user()->tenant_id;

        return WorkOrder::forTenant($tenantId)
            ->with(['customer', 'vehicle', 'insuranceCompany'])
            ->when(! $this->showKicked, fn($q) => $q->where('kicked', false))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType,   fn($q) => $q->where('job_type', $this->filterType))
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

    public function render()
    {
        return view('livewire.work-orders.work-order-list');
    }
}
