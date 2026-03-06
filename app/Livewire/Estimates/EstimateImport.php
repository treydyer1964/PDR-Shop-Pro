<?php

namespace App\Livewire\Estimates;

use App\Models\Customer;
use App\Models\InsuranceCompany;
use App\Services\EstimateOcrService;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class EstimateImport extends Component
{
    use WithFileUploads;

    public int $step = 1;

    // Step 1 — file upload
    public $file = null;

    // Step 2 — extracted fields (all editable)
    public string $customerFirstName = '';
    public string $customerLastName  = '';
    public string $customerPhone     = '';
    public string $customerEmail     = '';
    public string $customerAddress   = '';
    public string $customerCity      = '';
    public string $customerState     = '';
    public string $customerZip       = '';

    public string $vehicleVin      = '';
    public string $vehicleYear     = '';
    public string $vehicleMake     = '';
    public string $vehicleModel    = '';
    public string $vehicleColor    = '';
    public string $vehicleOdometer = '';

    public string  $insuranceCompany = '';
    public string  $claimNumber      = '';
    public string  $policyNumber     = '';
    public string  $adjusterName     = '';
    public string  $adjusterPhone    = '';
    public string  $adjusterEmail    = '';
    public ?int    $supplementNumber = null;

    // Customer match state
    public ?int $matchedCustomerId  = null;
    public bool $useMatchedCustomer = true;

    public ?string $extractError = null;

    // ── Computed ───────────────────────────────────────────────────────────────

    #[Computed]
    public function matchedCustomer(): ?Customer
    {
        return $this->matchedCustomerId
            ? Customer::find($this->matchedCustomerId)
            : null;
    }

    // ── Step 1: extract ────────────────────────────────────────────────────────

    public function extract(): void
    {
        $this->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $this->extractError = null;

        try {
            $service  = app(EstimateOcrService::class);
            $data     = $service->extract(
                $this->file->getRealPath(),
                $this->file->getMimeType() ?? $this->file->getClientMimeType()
            );

            $this->customerFirstName = $data['customer_first_name'] ?? '';
            $this->customerLastName  = $data['customer_last_name']  ?? '';
            $this->customerPhone     = $data['customer_phone']      ?? '';
            $this->customerEmail     = $data['customer_email']      ?? '';
            $this->customerAddress   = $data['customer_address']    ?? '';
            $this->customerCity      = $data['customer_city']       ?? '';
            $this->customerState     = $data['customer_state']      ?? '';
            $this->customerZip       = $data['customer_zip']        ?? '';
            $this->vehicleVin        = $data['vehicle_vin']         ?? '';
            $this->vehicleYear       = $data['vehicle_year']        ?? '';
            $this->vehicleMake       = $data['vehicle_make']        ?? '';
            $this->vehicleModel      = $data['vehicle_model']       ?? '';
            $this->vehicleColor      = $data['vehicle_color']       ?? '';
            $this->vehicleOdometer   = $data['vehicle_odometer']    ?? '';
            $this->insuranceCompany  = $data['insurance_company']   ?? '';
            $this->claimNumber       = $data['claim_number']        ?? '';
            $this->policyNumber      = $data['policy_number']       ?? '';
            $this->adjusterName      = $data['adjuster_name']       ?? '';
            $this->adjusterPhone     = $data['adjuster_phone']      ?? '';
            $this->adjusterEmail     = $data['adjuster_email']      ?? '';
            $this->supplementNumber  = $data['supplement_number'];

            // Try to match an existing customer by phone
            $this->matchedCustomerId  = null;
            $this->useMatchedCustomer = true;

            if ($this->customerPhone) {
                $digits = preg_replace('/\D/', '', $this->customerPhone);
                $match  = Customer::where('tenant_id', auth()->user()->tenant_id)
                    ->whereRaw("REGEXP_REPLACE(phone, '[^0-9]', '') = ?", [$digits])
                    ->first();
                if ($match) {
                    $this->matchedCustomerId = $match->id;
                }
            }

            $this->step = 2;

        } catch (\Throwable $e) {
            $this->extractError = $e->getMessage();
        }
    }

    public function startOver(): void
    {
        $this->reset();
        $this->step = 1;
    }

    // ── Step 2: create work order ──────────────────────────────────────────────

    public function createWorkOrder(): void
    {
        $this->validate([
            'customerLastName' => 'required|string|max:100',
            'vehicleMake'      => 'required|string|max:50',
            'vehicleModel'     => 'required|string|max:50',
            'vehicleYear'      => 'required|integer|min:1990|max:2035',
        ]);

        $tenantId = auth()->user()->tenant_id;

        // ── Resolve customer ───────────────────────────────────────────────────
        if ($this->matchedCustomerId && $this->useMatchedCustomer) {
            $customerId = $this->matchedCustomerId;
        } else {
            $customer = Customer::create([
                'tenant_id'  => $tenantId,
                'first_name' => $this->customerFirstName ?: null,
                'last_name'  => $this->customerLastName,
                'phone'      => $this->customerPhone    ?: null,
                'email'      => $this->customerEmail    ?: null,
                'address'    => $this->customerAddress  ?: null,
                'city'       => $this->customerCity     ?: null,
                'state'      => $this->customerState    ?: null,
                'zip'        => $this->customerZip      ?: null,
            ]);
            $customerId = $customer->id;
        }

        // ── Try to match insurance company by name ─────────────────────────────
        $insuranceCompanyId = null;
        if ($this->insuranceCompany) {
            $search = strtolower($this->insuranceCompany);
            $ic = InsuranceCompany::where('active', true)
                ->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?',       ["%{$search}%"])
                      ->orWhereRaw('LOWER(short_name) LIKE ?', ["%{$search}%"]);
                })
                ->first();
            $insuranceCompanyId = $ic?->id;
        }

        // ── Store prefill in session for WO wizard ─────────────────────────────
        session()->put('estimate_prefill', [
            'vehicle_vin'            => $this->vehicleVin      ?: null,
            'vehicle_year'           => $this->vehicleYear     ?: null,
            'vehicle_make'           => $this->vehicleMake     ?: null,
            'vehicle_model'          => $this->vehicleModel    ?: null,
            'vehicle_color'          => $this->vehicleColor    ?: null,
            'vehicle_odometer'       => $this->vehicleOdometer ?: null,
            'insurance_company_id'   => $insuranceCompanyId,
            'insurance_company_name' => $this->insuranceCompany ?: null,
            'claim_number'           => $this->claimNumber     ?: null,
            'policy_number'          => $this->policyNumber    ?: null,
            'adjuster_name'          => $this->adjusterName    ?: null,
            'adjuster_phone'         => $this->adjusterPhone   ?: null,
            'adjuster_email'         => $this->adjusterEmail   ?: null,
        ]);

        $this->redirect(
            route('work-orders.create', ['customer_id' => $customerId]),
            navigate: true
        );
    }

    public function render()
    {
        return view('livewire.estimates.estimate-import');
    }
}
