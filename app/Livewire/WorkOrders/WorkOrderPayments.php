<?php

namespace App\Livewire\WorkOrders;

use App\Enums\PaymentMethod;
use App\Enums\PaymentSource;
use App\Models\WorkOrder;
use App\Models\WorkOrderPayment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class WorkOrderPayments extends Component
{
    public WorkOrder $workOrder;

    // Form state
    public bool $showForm = false;
    public ?int $editingId = null;

    public string $source = 'INSURANCE';
    public string $method = 'CHECK';
    public string $amount = '';
    public string $receivedOn = '';
    public string $reference = '';
    public string $notes = '';

    // Close confirmation
    public bool $showCloseConfirm = false;

    #[Computed]
    public function payments()
    {
        return $this->workOrder->payments()->with('creator')->get();
    }

    #[Computed]
    public function sources(): array
    {
        return PaymentSource::cases();
    }

    #[Computed]
    public function methods(): array
    {
        return PaymentMethod::cases();
    }

    #[Computed]
    public function totalPaid(): float
    {
        return (float) $this->payments->sum('amount');
    }

    #[Computed]
    public function totalPaidInsurance(): float
    {
        return (float) $this->payments->where('source', PaymentSource::Insurance)->sum('amount');
    }

    #[Computed]
    public function totalPaidCustomer(): float
    {
        return (float) $this->payments->where('source', PaymentSource::Customer)->sum('amount');
    }

    #[Computed]
    public function balanceOwed(): ?float
    {
        if ($this->workOrder->invoice_total === null) {
            return null;
        }

        return (float) $this->workOrder->invoice_total - $this->totalPaid;
    }

    public function openAdd(): void
    {
        $this->resetForm();
        $this->receivedOn = now()->format('Y-m-d');
        $this->showForm   = true;
        $this->editingId  = null;
    }

    public function openEdit(int $id): void
    {
        $payment = WorkOrderPayment::findOrFail($id);

        $this->editingId   = $id;
        $this->source      = $payment->source->value;
        $this->method      = $payment->method->value;
        $this->amount      = (string) $payment->amount;
        $this->receivedOn  = $payment->received_on?->format('Y-m-d') ?? '';
        $this->reference   = $payment->reference ?? '';
        $this->notes       = $payment->notes ?? '';
        $this->showForm    = true;
    }

    public function save(): void
    {
        $this->validate([
            'source'     => 'required|in:INSURANCE,CUSTOMER,OTHER',
            'method'     => 'required|in:CHECK,CARD,CASH,ACH,OTHER',
            'amount'     => 'required|numeric|min:0.01',
            'receivedOn' => 'nullable|date',
            'reference'  => 'nullable|string|max:100',
            'notes'      => 'nullable|string|max:255',
        ]);

        $data = [
            'tenant_id'     => $this->workOrder->tenant_id,
            'work_order_id' => $this->workOrder->id,
            'source'        => $this->source,
            'method'        => $this->method,
            'amount'        => $this->amount,
            'received_on'   => $this->receivedOn ?: null,
            'reference'     => $this->reference ?: null,
            'notes'         => $this->notes ?: null,
            'created_by'    => Auth::id(),
        ];

        if ($this->editingId) {
            WorkOrderPayment::findOrFail($this->editingId)->update($data);
        } else {
            WorkOrderPayment::create($data);
        }

        $this->resetForm();
        $this->workOrder->refresh();
        unset($this->payments);
    }

    public function delete(int $id): void
    {
        WorkOrderPayment::where('tenant_id', $this->workOrder->tenant_id)
            ->findOrFail($id)
            ->delete();

        $this->workOrder->refresh();
        unset($this->payments);
    }

    public function closeWorkOrder(): void
    {
        $this->workOrder->close();
        $this->workOrder->refresh();
        $this->showCloseConfirm = false;
    }

    public function reopenWorkOrder(): void
    {
        $this->workOrder->reopen();
        $this->workOrder->refresh();
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->showForm   = false;
        $this->editingId  = null;
        $this->source     = 'INSURANCE';
        $this->method     = 'CHECK';
        $this->amount     = '';
        $this->receivedOn = '';
        $this->reference  = '';
        $this->notes      = '';
    }

    public function render()
    {
        return view('livewire.work-orders.work-order-payments');
    }
}
