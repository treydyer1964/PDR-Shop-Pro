<?php

namespace App\Livewire\Appointments;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentType;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AppointmentList extends Component
{
    public string $filterStatus = 'upcoming';  // upcoming|all|completed|cancelled
    public string $filterTypeId = '';
    public string $filterDate   = '';

    #[Computed]
    public function appointments()
    {
        $tenantId = auth()->user()->tenant_id;

        $query = Appointment::forTenant($tenantId)
            ->with(['type', 'workOrder.customer', 'workOrder.vehicle'])
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
