<?php

namespace App\Livewire\WorkOrders;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\WorkOrder;
use Livewire\Attributes\Computed;
use Livewire\Component;

class WorkOrderAppointments extends Component
{
    public WorkOrder $workOrder;

    // Add form
    public bool   $showForm       = false;
    public string $typeId         = '';
    public string $scheduledDate  = '';
    public string $scheduledTime  = '09:00';
    public string $notes          = '';

    // Edit
    public ?int   $editingId      = null;

    public function mount(WorkOrder $workOrder): void
    {
        $this->workOrder = $workOrder;
    }

    #[Computed]
    public function appointments()
    {
        return $this->workOrder->appointments()
            ->with('type')
            ->orderBy('scheduled_at')
            ->get();
    }

    #[Computed]
    public function types()
    {
        return AppointmentType::forTenant(auth()->user()->tenant_id)
            ->active()
            ->orderBy('sort_order')
            ->get();
    }

    public function openAdd(): void
    {
        $this->reset(['typeId', 'scheduledDate', 'notes', 'editingId']);
        $this->scheduledTime = '09:00';
        $this->scheduledDate = now()->toDateString();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $appt = Appointment::findOrFail($id);
        abort_unless($appt->work_order_id === $this->workOrder->id, 403);

        $this->editingId     = $id;
        $this->typeId        = (string) $appt->appointment_type_id;
        $this->scheduledDate = $appt->scheduled_at->toDateString();
        $this->scheduledTime = $appt->scheduled_at->format('H:i');
        $this->notes         = $appt->notes ?? '';
        $this->showForm      = true;
    }

    public function save(): void
    {
        $this->validate([
            'typeId'        => 'required|exists:appointment_types,id',
            'scheduledDate' => 'required|date',
            'scheduledTime' => 'required',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $scheduledAt = \Carbon\Carbon::parse("{$this->scheduledDate} {$this->scheduledTime}");

        $fields = [
            'appointment_type_id' => (int) $this->typeId,
            'scheduled_at'        => $scheduledAt,
            'notes'               => $this->notes ?: null,
        ];

        if ($this->editingId) {
            $appt = Appointment::findOrFail($this->editingId);
            abort_unless($appt->work_order_id === $this->workOrder->id, 403);
            $appt->update($fields);
        } else {
            Appointment::create(array_merge($fields, [
                'tenant_id'     => $this->workOrder->tenant_id,
                'work_order_id' => $this->workOrder->id,
                'status'        => AppointmentStatus::Scheduled->value,
                'created_by'    => auth()->id(),
            ]));
        }

        $this->showForm  = false;
        $this->editingId = null;
        unset($this->appointments);
    }

    public function updateStatus(int $id, string $status): void
    {
        $appt = Appointment::findOrFail($id);
        abort_unless($appt->work_order_id === $this->workOrder->id, 403);

        $newStatus = AppointmentStatus::from($status);
        $appt->update([
            'status'       => $newStatus->value,
            'completed_at' => $newStatus === AppointmentStatus::Completed ? now() : null,
        ]);

        unset($this->appointments);
    }

    public function delete(int $id): void
    {
        $appt = Appointment::findOrFail($id);
        abort_unless($appt->work_order_id === $this->workOrder->id, 403);
        $appt->delete();
        unset($this->appointments);
    }

    public function cancel(): void
    {
        $this->showForm  = false;
        $this->editingId = null;
    }

    public function render()
    {
        return view('livewire.work-orders.work-order-appointments');
    }
}
