<?php

namespace App\Livewire\WorkOrders;

use App\Models\WorkOrder;
use App\Services\CommissionService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class WorkOrderCommissions extends Component
{
    public WorkOrder $workOrder;

    public array $errors = [];

    public function mount(WorkOrder $workOrder): void
    {
        $this->workOrder = $workOrder;
    }

    #[Computed]
    public function commissions()
    {
        return $this->workOrder->commissions()->with('user')->orderBy('role')->orderBy('id')->get();
    }

    #[Computed]
    public function totalCommissions(): float
    {
        return $this->commissions->sum('amount');
    }

    public function calculate(): void
    {
        $service = new CommissionService();
        $this->errors = $service->calculate($this->workOrder);

        if (empty($this->errors)) {
            $this->workOrder->load('commissions.user', 'expenses.category', 'events');
        }
    }

    public function lock(): void
    {
        if ($this->commissions->isEmpty()) {
            $this->errors = ['No commissions to lock. Calculate commissions first.'];
            return;
        }

        $this->workOrder->lockCommissions();
        $this->workOrder->refresh();
        $this->errors = [];
    }

    public function unlock(): void
    {
        $this->workOrder->unlockCommissions();
        $this->workOrder->refresh();
        $this->errors = [];
    }

    public function render()
    {
        return view('livewire.work-orders.work-order-commissions');
    }
}
