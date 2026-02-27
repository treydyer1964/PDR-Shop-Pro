<?php

namespace App\Livewire\WorkOrders;

use App\Models\RentalProvider;
use App\Models\RentalReimbursement;
use App\Models\RentalSegment;
use App\Models\RentalVehicle;
use App\Models\WorkOrder;
use App\Models\WorkOrderRental;
use App\Services\RentalService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class WorkOrderRentals extends Component
{
    public WorkOrder $workOrder;

    // Assignment form
    public bool   $showAssignForm   = false;
    public string $vehicleId        = '';
    public string $providerId       = '';
    public bool   $hasInsurance     = false;
    public string $insuranceDailyRate = '';
    public string $assignNotes      = '';

    // Segment form
    public ?int   $activeRentalId   = null;   // which WorkOrderRental we're adding segment to
    public string $segStartDate     = '';
    public string $segEndDate       = '';
    public string $segNotes         = '';
    public bool   $showSegmentForm  = false;

    // Reimbursement form
    public bool   $showReimburseForm        = false;
    public string $insuranceAmountReceived  = '';
    public string $reimburseNotes          = '';
    public ?int   $reimburseRentalId        = null;

    public function mount(WorkOrder $workOrder): void
    {
        $this->workOrder = $workOrder;

        // Re-sync expense on load — open segments accrue daily, so the
        // running total needs refreshing each time the page is viewed.
        $existing = WorkOrderRental::where('work_order_id', $workOrder->id)
            ->with(['vehicle', 'segments'])
            ->first();
        if ($existing) {
            app(RentalService::class)->syncExpense($existing);
        }
    }

    #[Computed]
    public function rental(): ?WorkOrderRental
    {
        return WorkOrderRental::where('work_order_id', $this->workOrder->id)
            ->with(['vehicle', 'provider', 'segments', 'reimbursement'])
            ->first();
    }

    #[Computed]
    public function vehicles()
    {
        return RentalVehicle::forTenant(auth()->user()->tenant_id)
            ->active()
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function providers()
    {
        return RentalProvider::forTenant(auth()->user()->tenant_id)
            ->active()
            ->orderBy('name')
            ->get();
    }

    // ── Assign a rental vehicle ────────────────────────────────────────────────

    public function assignRental(): void
    {
        $this->validate([
            'vehicleId'           => 'nullable|exists:rental_vehicles,id',
            'providerId'          => 'nullable|exists:rental_providers,id',
            'insuranceDailyRate'  => 'nullable|numeric|min:0',
            'assignNotes'         => 'nullable|string|max:1000',
        ]);

        $rental = WorkOrderRental::create([
            'tenant_id'            => $this->workOrder->tenant_id,
            'work_order_id'        => $this->workOrder->id,
            'rental_vehicle_id'    => $this->vehicleId ?: null,
            'rental_provider_id'   => $this->providerId ?: null,
            'has_insurance_coverage' => $this->hasInsurance,
            'insurance_daily_rate' => $this->hasInsurance && $this->insuranceDailyRate
                ? (float) $this->insuranceDailyRate
                : null,
            'notes'                => $this->assignNotes ?: null,
        ]);

        $this->reset(['vehicleId', 'providerId', 'hasInsurance', 'insuranceDailyRate', 'assignNotes']);
        $this->showAssignForm = false;

        unset($this->rental);
    }

    public function updateRentalInsurance(): void
    {
        $rental = $this->rental;
        if (! $rental) return;

        $this->validate([
            'hasInsurance'        => 'boolean',
            'insuranceDailyRate'  => 'nullable|numeric|min:0',
        ]);

        $rental->update([
            'has_insurance_coverage' => $this->hasInsurance,
            'insurance_daily_rate'   => $this->hasInsurance && $this->insuranceDailyRate
                ? (float) $this->insuranceDailyRate
                : null,
        ]);

        unset($this->rental);
    }

    public function removeRental(): void
    {
        $rental = $this->rental;
        if (! $rental) return;

        $rental->segments()->delete();
        $rental->reimbursement()?->delete();
        $rental->delete();

        app(RentalService::class)->removeExpense($this->workOrder);

        unset($this->rental);
    }

    // ── Segments ───────────────────────────────────────────────────────────────

    public function openSegmentForm(int $rentalId): void
    {
        $this->activeRentalId  = $rentalId;
        $this->segStartDate    = now()->toDateString();
        $this->segEndDate      = '';
        $this->segNotes        = '';
        $this->showSegmentForm = true;
    }

    public function addSegment(): void
    {
        $this->validate([
            'segStartDate' => 'required|date',
            'segEndDate'   => 'nullable|date|after_or_equal:segStartDate',
            'segNotes'     => 'nullable|string|max:500',
        ], [
            'segEndDate.after_or_equal' => 'Return date must be on or after the pickup date.',
        ]);

        $rental = WorkOrderRental::findOrFail($this->activeRentalId);

        $segment = RentalSegment::create([
            'tenant_id'           => $this->workOrder->tenant_id,
            'work_order_rental_id' => $rental->id,
            'start_date'          => $this->segStartDate,
            'end_date'            => $this->segEndDate ?: null,
            'notes'               => $this->segNotes ?: null,
        ]);

        $rental->load('segments', 'vehicle');
        app(RentalService::class)->syncExpense($rental);

        $this->showSegmentForm = false;
        $this->activeRentalId  = null;
        $this->reset(['segStartDate', 'segEndDate', 'segNotes']);

        unset($this->rental);
    }

    public function closeSegment(int $segmentId): void
    {
        $segment = RentalSegment::findOrFail($segmentId);
        $segment->update(['end_date' => now()->toDateString()]);

        $rental = $segment->workOrderRental()->with('segments', 'vehicle')->first();
        app(RentalService::class)->syncExpense($rental);

        unset($this->rental);
    }

    public function deleteSegment(int $segmentId): void
    {
        $segment = RentalSegment::findOrFail($segmentId);
        $rentalId = $segment->work_order_rental_id;
        $segment->delete();

        $rental = WorkOrderRental::with('segments', 'vehicle')->findOrFail($rentalId);
        app(RentalService::class)->syncExpense($rental);

        unset($this->rental);
    }

    // ── Reimbursement ──────────────────────────────────────────────────────────

    public function openReimburseForm(int $rentalId): void
    {
        $this->reimburseRentalId      = $rentalId;
        $this->insuranceAmountReceived = '';
        $this->reimburseNotes         = '';
        $this->showReimburseForm      = true;
    }

    public function recordReimbursement(): void
    {
        $this->validate([
            'insuranceAmountReceived' => 'required|numeric|min:0.01',
            'reimburseNotes'          => 'nullable|string|max:1000',
        ], [
            'insuranceAmountReceived.required' => 'Enter the amount received from insurance.',
            'insuranceAmountReceived.min'      => 'Amount must be greater than zero.',
        ]);

        $rental = WorkOrderRental::with('reimbursement')
            ->findOrFail($this->reimburseRentalId);

        if ($rental->reimbursement) {
            $rental->reimbursement->update([
                'insurance_amount_received' => (float) $this->insuranceAmountReceived,
                'notes'                    => $this->reimburseNotes ?: null,
                'recorded_by'              => auth()->id(),
            ]);
        } else {
            RentalReimbursement::create([
                'tenant_id'                => $this->workOrder->tenant_id,
                'work_order_id'            => $this->workOrder->id,
                'work_order_rental_id'     => $rental->id,
                'insurance_amount_received' => (float) $this->insuranceAmountReceived,
                'notes'                    => $this->reimburseNotes ?: null,
                'recorded_by'              => auth()->id(),
            ]);
        }

        $this->showReimburseForm  = false;
        $this->reimburseRentalId  = null;
        $this->reset(['insuranceAmountReceived', 'reimburseNotes']);

        unset($this->rental);
    }

    public function deleteReimbursement(int $reimbursementId): void
    {
        RentalReimbursement::findOrFail($reimbursementId)->delete();
        unset($this->rental);
    }

    #[Computed]
    public function reimbursementBreakdown(): array
    {
        $rental = $this->rental;
        if (! $rental?->reimbursement) return [];

        return app(RentalService::class)->calculateReimbursements($rental);
    }

    public function render()
    {
        return view('livewire.work-orders.work-order-rentals');
    }
}
