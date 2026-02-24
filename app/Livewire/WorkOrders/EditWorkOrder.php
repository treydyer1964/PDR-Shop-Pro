<?php

namespace App\Livewire\WorkOrders;

use App\Enums\WorkOrderJobType;
use App\Models\InsuranceCompany;
use App\Models\Location;
use App\Models\WorkOrder;
use Livewire\Attributes\Computed;
use Livewire\Component;

class EditWorkOrder extends Component
{
    public WorkOrder $workOrder;

    public int    $location_id      = 0;
    public string $notes            = '';
    public string $invoice_total    = '';

    // Insurance fields
    public ?int  $insurance_company_id   = null;
    public string $claim_number          = '';
    public string $policy_number         = '';
    public string $adjuster_name         = '';
    public string $adjuster_phone        = '';
    public string $adjuster_email        = '';
    public string $deductible            = '';
    public bool  $insurance_pre_inspected = false;
    public bool  $has_rental_coverage    = false;

    public function mount(WorkOrder $workOrder): void
    {
        abort_unless($workOrder->tenant_id === auth()->user()->tenant_id, 403);

        $this->workOrder = $workOrder;

        $this->location_id             = $workOrder->location_id;
        $this->notes                   = $workOrder->notes ?? '';
        $this->invoice_total           = $workOrder->invoice_total ?? '';
        $this->insurance_company_id    = $workOrder->insurance_company_id;
        $this->claim_number            = $workOrder->claim_number ?? '';
        $this->policy_number           = $workOrder->policy_number ?? '';
        $this->adjuster_name           = $workOrder->adjuster_name ?? '';
        $this->adjuster_phone          = $workOrder->adjuster_phone ?? '';
        $this->adjuster_email          = $workOrder->adjuster_email ?? '';
        $this->deductible              = $workOrder->deductible ?? '';
        $this->insurance_pre_inspected = (bool) $workOrder->insurance_pre_inspected;
        $this->has_rental_coverage     = (bool) $workOrder->has_rental_coverage;
    }

    #[Computed]
    public function locations()
    {
        return Location::where('tenant_id', auth()->user()->tenant_id)
            ->where('active', true)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function insuranceCompanies()
    {
        return InsuranceCompany::active()->orderBy('name')->get();
    }

    #[Computed]
    public function isInsurance(): bool
    {
        return $this->workOrder->job_type === WorkOrderJobType::Insurance;
    }

    public function save(): void
    {
        $rules = [
            'location_id'   => 'required|integer',
            'notes'         => 'nullable|string|max:5000',
            'invoice_total' => 'nullable|numeric|min:0',
        ];

        if ($this->isInsurance) {
            $rules += [
                'insurance_company_id' => 'nullable|integer',
                'claim_number'         => 'nullable|string|max:100',
                'policy_number'        => 'nullable|string|max:100',
                'adjuster_name'        => 'nullable|string|max:100',
                'adjuster_phone'       => 'nullable|string|max:20',
                'adjuster_email'       => 'nullable|email|max:100',
                'deductible'           => 'nullable|numeric|min:0',
            ];
        }

        $this->validate($rules);

        $data = [
            'location_id'   => $this->location_id,
            'notes'         => $this->notes ?: null,
            'invoice_total' => $this->invoice_total !== '' ? $this->invoice_total : null,
        ];

        if ($this->isInsurance) {
            $data += [
                'insurance_company_id'    => $this->insurance_company_id ?: null,
                'claim_number'            => $this->claim_number ?: null,
                'policy_number'           => $this->policy_number ?: null,
                'adjuster_name'           => $this->adjuster_name ?: null,
                'adjuster_phone'          => $this->adjuster_phone ?: null,
                'adjuster_email'          => $this->adjuster_email ?: null,
                'deductible'              => $this->deductible !== '' ? $this->deductible : null,
                'insurance_pre_inspected' => $this->insurance_pre_inspected,
                'has_rental_coverage'     => $this->has_rental_coverage,
            ];
        }

        $this->workOrder->update($data);

        session()->flash('success', 'Work order updated.');
        $this->redirect(route('work-orders.show', $this->workOrder), navigate: true);
    }

    public function render()
    {
        return view('livewire.work-orders.edit-work-order');
    }
}
