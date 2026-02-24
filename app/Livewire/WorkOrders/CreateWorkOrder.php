<?php

namespace App\Livewire\WorkOrders;

use App\Enums\WorkOrderJobType;
use App\Enums\WorkOrderStatus;
use App\Models\Customer;
use App\Models\InsuranceCompany;
use App\Models\Location;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatusLog;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CreateWorkOrder extends Component
{
    // ── Wizard state ───────────────────────────────────────────────────────────
    public int $step = 1;
    public int $totalSteps = 4;

    // Step 1 — Job Type
    public string $job_type = '';

    // Step 2 — Customer
    public string $customerSearch = '';
    public ?int $customer_id = null;
    public bool $creatingNewCustomer = false;
    // New customer fields
    public string $cFirst = '';
    public string $cLast  = '';
    public string $cPhone = '';
    public string $cEmail = '';

    // Step 3 — Vehicle
    public ?int $vehicle_id = null;
    public bool $creatingNewVehicle = false;
    // New vehicle fields
    public string $vVin       = '';
    public string $vYear      = '';
    public string $vMake      = '';
    public string $vModel     = '';
    public string $vTrim      = '';
    public string $vColor     = '';
    public string $vPlate     = '';

    // Step 4 — Job Details
    public int $location_id = 0;
    public string $notes    = '';
    // Insurance-specific
    public ?int  $insurance_company_id  = null;
    public string $claim_number         = '';
    public string $policy_number        = '';
    public string $adjuster_name        = '';
    public string $adjuster_phone       = '';
    public string $adjuster_email       = '';
    public string $deductible           = '';
    public bool  $insurance_pre_inspected = false;
    public bool  $has_rental_coverage   = false;

    // ── Computed ───────────────────────────────────────────────────────────────

    #[Computed]
    public function jobTypes(): array
    {
        return WorkOrderJobType::cases();
    }

    #[Computed]
    public function customerResults()
    {
        if (strlen($this->customerSearch) < 2) {
            return collect();
        }

        return Customer::where('tenant_id', auth()->user()->tenant_id)
            ->where(function ($q) {
                $q->where('first_name', 'like', "%{$this->customerSearch}%")
                  ->orWhere('last_name',  'like', "%{$this->customerSearch}%")
                  ->orWhere('phone',      'like', "%{$this->customerSearch}%");
            })
            ->orderBy('last_name')
            ->limit(8)
            ->get();
    }

    #[Computed]
    public function selectedCustomer(): ?Customer
    {
        return $this->customer_id
            ? Customer::find($this->customer_id)
            : null;
    }

    #[Computed]
    public function customerVehicles()
    {
        return $this->customer_id
            ? Vehicle::where('customer_id', $this->customer_id)
                ->where('tenant_id', auth()->user()->tenant_id)
                ->orderByDesc('year')
                ->get()
            : collect();
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
        return $this->job_type === WorkOrderJobType::Insurance->value;
    }

    // ── Step navigation ────────────────────────────────────────────────────────

    /** Step 1: clicking a job type immediately selects it and advances */
    public function selectJobType(string $type): void
    {
        $this->job_type = $type;
        $this->step = 2;
    }

    public function nextStep(): void
    {
        if ($this->step === 2) {
            $this->validateStep2();
        } elseif ($this->step === 3) {
            $this->validateStep3();
        }
        $this->step++;
    }

    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    private function validateStep2(): void
    {
        if ($this->creatingNewCustomer) {
            $this->validate([
                'cFirst' => 'required|string|max:100',
                'cLast'  => 'required|string|max:100',
                'cPhone' => 'nullable|string|max:20',
                'cEmail' => 'nullable|email|max:255',
            ]);
            $customer = Customer::create([
                'tenant_id'  => auth()->user()->tenant_id,
                'first_name' => $this->cFirst,
                'last_name'  => $this->cLast,
                'phone'      => $this->cPhone ?: null,
                'email'      => $this->cEmail ?: null,
            ]);
            $this->customer_id = $customer->id;
            $this->creatingNewCustomer = false;
        } else {
            $this->validate(['customer_id' => 'required|integer']);
        }
    }

    private function validateStep3(): void
    {
        if ($this->creatingNewVehicle) {
            $this->validate([
                'vVin'  => 'nullable|string|size:17',
                'vMake' => 'required|string|max:50',
                'vModel'=> 'required|string|max:50',
                'vYear' => 'required|integer|min:1990|max:2030',
            ]);
            $vehicle = Vehicle::create([
                'tenant_id'   => auth()->user()->tenant_id,
                'customer_id' => $this->customer_id,
                'vin'         => $this->vVin  ?: null,
                'year'        => $this->vYear ?: null,
                'make'        => $this->vMake ?: null,
                'model'       => $this->vModel?: null,
                'trim'        => $this->vTrim ?: null,
                'color'       => $this->vColor?: null,
                'plate'       => $this->vPlate?: null,
            ]);
            $this->vehicle_id = $vehicle->id;
            $this->creatingNewVehicle = false;
        } else {
            $this->validate(['vehicle_id' => 'required|integer']);
        }
    }

    // ── Customer selection ─────────────────────────────────────────────────────

    public function selectCustomer(int $id): void
    {
        $this->customer_id = $id;
        $this->customerSearch = '';
        $this->vehicle_id = null;
        unset($this->customerResults);
    }

    public function clearCustomer(): void
    {
        $this->customer_id = null;
        $this->vehicle_id  = null;
    }

    public function startNewCustomer(): void
    {
        $this->creatingNewCustomer = true;
        $this->customer_id = null;
    }

    // ── Vehicle selection ──────────────────────────────────────────────────────

    public function selectVehicle(int $id): void
    {
        $this->vehicle_id = $id;
        $this->creatingNewVehicle = false;
    }

    public function startNewVehicle(): void
    {
        $this->creatingNewVehicle = true;
        $this->vehicle_id = null;
    }

    // VIN decoded via NHTSA (called from Alpine.js fetch)
    public function vinDecoded(array $data): void
    {
        $this->vYear  = $data['year']  ?? $this->vYear;
        $this->vMake  = $data['make']  ?? $this->vMake;
        $this->vModel = $data['model'] ?? $this->vModel;
        $this->vTrim  = $data['trim']  ?? $this->vTrim;
    }

    // ── Final submit ───────────────────────────────────────────────────────────

    public function create(): void
    {
        $tenantId = auth()->user()->tenant_id;

        $this->validate([
            'job_type'    => 'required|in:insurance,customer_pay,wholesale',
            'customer_id' => 'required|integer',
            'vehicle_id'  => 'required|integer',
            'location_id' => 'required|integer',
        ]);

        $roNumber = WorkOrder::generateRoNumber($tenantId);

        $wo = WorkOrder::create([
            'tenant_id'                => $tenantId,
            'location_id'              => $this->location_id,
            'customer_id'              => $this->customer_id,
            'vehicle_id'               => $this->vehicle_id,
            'ro_number'                => $roNumber,
            'job_type'                 => $this->job_type,
            'status'                   => WorkOrderStatus::ToBeAcquired->value,
            'notes'                    => $this->notes ?: null,
            'insurance_company_id'     => $this->isInsurance ? ($this->insurance_company_id ?: null) : null,
            'claim_number'             => $this->isInsurance ? ($this->claim_number ?: null)  : null,
            'policy_number'            => $this->isInsurance ? ($this->policy_number ?: null) : null,
            'adjuster_name'            => $this->isInsurance ? ($this->adjuster_name ?: null) : null,
            'adjuster_phone'           => $this->isInsurance ? ($this->adjuster_phone ?: null): null,
            'adjuster_email'           => $this->isInsurance ? ($this->adjuster_email ?: null): null,
            'deductible'               => $this->isInsurance && $this->deductible ? (float) $this->deductible : null,
            'insurance_pre_inspected'  => $this->isInsurance && $this->insurance_pre_inspected,
            'has_rental_coverage'      => $this->isInsurance && $this->has_rental_coverage,
        ]);

        // Open the first status log entry
        WorkOrderStatusLog::create([
            'work_order_id' => $wo->id,
            'tenant_id'     => $tenantId,
            'user_id'       => auth()->id(),
            'status'        => WorkOrderStatus::ToBeAcquired->value,
            'entered_at'    => now(),
        ]);

        session()->flash('success', "Work order {$roNumber} created.");
        $this->redirect(route('work-orders.show', $wo), navigate: true);
    }

    public function render()
    {
        return view('livewire.work-orders.create-work-order');
    }
}
