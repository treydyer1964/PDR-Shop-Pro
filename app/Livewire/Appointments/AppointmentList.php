<?php

namespace App\Livewire\Appointments;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\WorkOrder;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AppointmentList extends Component
{
    public string $filterStatus = 'upcoming';  // upcoming|all|completed|cancelled
    public string $filterTypeId = '';
    public string $filterDate   = '';

    // Create form
    public bool   $creating      = false;
    public int    $newTypeId      = 0;
    public string $newScheduledAt = '';
    public string $newNotes       = '';
    public string $woSearch       = '';
    public ?int   $newWorkOrderId = null;

    #[Computed]
    public function appointments()
    {
        $tenantId = auth()->user()->tenant_id;

        $query = Appointment::forTenant($tenantId)
            ->with(['type', 'workOrder.customer', 'workOrder.vehicle', 'lead'])
            ->orderBy('scheduled_at');

        match ($this->filterStatus) {
            'upcoming'  => $query->where('scheduled_at', '>=', now())
                                 ->where('status', AppointmentStatus::Scheduled->value),
            'completed' => $query->where('status', AppointmentStatus::Completed->value),
            'cancelled' => $query->whereIn('status', [
                                AppointmentStatus::Cancelled->value,
                                AppointmentStatus::NoShow->value,
                            ]),
            default     => null,  // 'all' — no extra filter
        };

        if ($this->filterTypeId) {
            $query->where('appointment_type_id', $this->filterTypeId);
        }

        if ($this->filterDate) {
            $query->whereDate('scheduled_at', $this->filterDate);
        }

        return $query->get();
    }

    #[Computed]
    public function types()
    {
        return AppointmentType::forTenant(auth()->user()->tenant_id)
            ->active()
            ->orderBy('sort_order')
            ->get();
    }

    #[Computed]
    public function woResults()
    {
        if (strlen($this->woSearch) < 2) {
            return collect();
        }

        return WorkOrder::forTenant(auth()->user()->tenant_id)
            ->with(['customer', 'vehicle'])
            ->where(function ($q) {
                $q->where('ro_number', 'like', "%{$this->woSearch}%")
                  ->orWhereHas('customer', fn($cq) =>
                      $cq->where('first_name', 'like', "%{$this->woSearch}%")
                         ->orWhere('last_name',  'like', "%{$this->woSearch}%")
                  );
            })
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();
    }

    #[Computed]
    public function selectedWorkOrder(): ?WorkOrder
    {
        return $this->newWorkOrderId
            ? WorkOrder::with(['customer', 'vehicle'])->find($this->newWorkOrderId)
            : null;
    }

    public function openCreate(): void
    {
        $this->creating       = true;
        $this->newTypeId      = 0;
        $this->newScheduledAt = '';
        $this->newNotes       = '';
        $this->woSearch       = '';
        $this->newWorkOrderId = null;
    }

    public function cancelCreate(): void
    {
        $this->creating = false;
    }

    public function selectWorkOrder(int $id): void
    {
        $this->newWorkOrderId = $id;
        $this->woSearch       = '';
        unset($this->woResults, $this->selectedWorkOrder);
    }

    public function clearWorkOrder(): void
    {
        $this->newWorkOrderId = null;
        unset($this->selectedWorkOrder);
    }

    public function saveAppointment(): void
    {
        $this->validate([
            'newTypeId'      => 'required|integer|min:1',
            'newScheduledAt' => 'required|date',
            'newNotes'       => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();

        Appointment::create([
            'tenant_id'           => $user->tenant_id,
            'work_order_id'       => $this->newWorkOrderId,
            'appointment_type_id' => $this->newTypeId,
            'scheduled_at'        => $this->newScheduledAt,
            'notes'               => $this->newNotes ?: null,
            'status'              => AppointmentStatus::Scheduled->value,
            'created_by'          => $user->id,
        ]);

        $this->creating = false;
        unset($this->appointments);
    }

    public function updateStatus(int $id, string $status): void
    {
        $appt = Appointment::findOrFail($id);
        abort_unless($appt->tenant_id === auth()->user()->tenant_id, 403);

        $newStatus = AppointmentStatus::from($status);
        $appt->update([
            'status'       => $newStatus->value,
            'completed_at' => $newStatus === AppointmentStatus::Completed ? now() : null,
        ]);

        unset($this->appointments);
    }

    public function render()
    {
        return view('livewire.appointments.appointment-list');
    }
}
