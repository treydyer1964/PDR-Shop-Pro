<?php

namespace App\Livewire\WorkOrders;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class WorkOrderCalendar extends Component
{
    #[Url(as: 'cal')]
    public string $calView = 'month'; // 'month' | 'week'

    #[Url(as: 'y')]
    public int $year = 0;

    #[Url(as: 'mo')]
    public int $month = 0;

    #[Url(as: 'ws')]
    public string $weekStart = '';

    public function mount(): void
    {
        if (! $this->year)      $this->year      = now()->year;
        if (! $this->month)     $this->month     = now()->month;
        if (! $this->weekStart) $this->weekStart = now()->startOfWeek(Carbon::SUNDAY)->format('Y-m-d');
    }

    // ── Navigation ────────────────────────────────────────────────────────────

    public function setCalView(string $view): void
    {
        $this->calView = $view;
    }

    public function prev(): void
    {
        if ($this->calView === 'month') {
            $d = Carbon::create($this->year, $this->month, 1)->subMonth();
            $this->year  = $d->year;
            $this->month = $d->month;
        } else {
            $this->weekStart = Carbon::parse($this->weekStart)->subWeek()->format('Y-m-d');
        }
        unset($this->calendarWeeks, $this->weekDays, $this->eventsByDate);
    }

    public function next(): void
    {
        if ($this->calView === 'month') {
            $d = Carbon::create($this->year, $this->month, 1)->addMonth();
            $this->year  = $d->year;
            $this->month = $d->month;
        } else {
            $this->weekStart = Carbon::parse($this->weekStart)->addWeek()->format('Y-m-d');
        }
        unset($this->calendarWeeks, $this->weekDays, $this->eventsByDate);
    }

    public function goToday(): void
    {
        $this->year      = now()->year;
        $this->month     = now()->month;
        $this->weekStart = now()->startOfWeek(Carbon::SUNDAY)->format('Y-m-d');
        unset($this->calendarWeeks, $this->weekDays, $this->eventsByDate);
    }

    // ── Computed ──────────────────────────────────────────────────────────────

    #[Computed]
    public function calendarWeeks(): array
    {
        $start = Carbon::create($this->year, $this->month, 1)->startOfWeek(Carbon::SUNDAY);
        $end   = Carbon::create($this->year, $this->month, 1)->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        $weeks = [];
        $day   = $start->copy();

        while ($day <= $end) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $week[] = $day->copy();
                $day->addDay();
            }
            $weeks[] = $week;
        }

        return $weeks;
    }

    #[Computed]
    public function weekDays(): array
    {
        $start = Carbon::parse($this->weekStart);
        $days  = [];
        for ($i = 0; $i < 7; $i++) {
            $days[] = $start->copy()->addDays($i);
        }
        return $days;
    }

    #[Computed]
    public function periodLabel(): string
    {
        if ($this->calView === 'month') {
            return Carbon::create($this->year, $this->month, 1)->format('F Y');
        }
        $start = Carbon::parse($this->weekStart);
        $end   = $start->copy()->addDays(6);
        if ($start->month === $end->month) {
            return $start->format('F j') . '–' . $end->format('j, Y');
        }
        return $start->format('M j') . '–' . $end->format('M j, Y');
    }

    #[Computed]
    public function eventsByDate(): array
    {
        if ($this->calView === 'month') {
            $from = Carbon::create($this->year, $this->month, 1)->startOfWeek(Carbon::SUNDAY);
            $to   = Carbon::create($this->year, $this->month, 1)->endOfMonth()->endOfWeek(Carbon::SATURDAY);
        } else {
            $from = Carbon::parse($this->weekStart);
            $to   = $from->copy()->addDays(6);
        }

        $user     = auth()->user();
        $tenantId = $user->tenant_id;

        // Work orders with expected_delivery_date in range
        $woQuery = WorkOrder::forTenant($tenantId)
            ->with(['customer', 'vehicle'])
            ->whereNotNull('expected_delivery_date')
            ->whereBetween('expected_delivery_date', [$from->toDateString(), $to->toDateString()])
            ->where('kicked', false);

        if (! $user->canSeeAllWorkOrders()) {
            $woQuery->whereHas('assignments', fn($q) => $q->where('user_id', $user->id));
        }

        // Appointments in range (excluding cancelled)
        $apptQuery = Appointment::forTenant($tenantId)
            ->with(['type', 'workOrder.customer'])
            ->whereBetween('scheduled_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->where('status', '!=', AppointmentStatus::Cancelled->value);

        if (! $user->canSeeAllWorkOrders()) {
            $apptQuery->whereHas('workOrder.assignments', fn($q) => $q->where('user_id', $user->id));
        }

        $events = [];

        foreach ($woQuery->get() as $wo) {
            $date = $wo->expected_delivery_date->format('Y-m-d');
            $events[$date][] = [
                'type'           => 'work_order',
                'label'          => $wo->ro_number,
                'sub'            => $wo->customer?->last_name ?? '',
                'vehicle'        => $wo->vehicle ? "{$wo->vehicle->year} {$wo->vehicle->make}" : '',
                'url'            => route('work-orders.show', $wo),
                'status_classes' => $wo->status->badgeClasses(),
                'status_label'   => $wo->status->label(),
            ];
        }

        foreach ($apptQuery->get() as $appt) {
            $date = $appt->scheduled_at->format('Y-m-d');
            $events[$date][] = [
                'type'          => 'appointment',
                'label'         => $appt->type?->name ?? 'Appointment',
                'sub'           => $appt->workOrder?->customer?->last_name ?? '',
                'time'          => $appt->scheduled_at->format('g:ia'),
                'url'           => $appt->work_order_id ? route('work-orders.show', $appt->work_order_id) : '#',
                'badge_classes' => $appt->type?->badgeClasses() ?? 'bg-blue-100 text-blue-700',
            ];
        }

        // Sort each day: appointments first (they have a time), then WOs
        foreach ($events as $date => $dayEvents) {
            usort($events[$date], fn($a, $b) => ($a['type'] === 'appointment' ? 0 : 1) - ($b['type'] === 'appointment' ? 0 : 1));
        }

        return $events;
    }

    public function render()
    {
        return view('livewire.work-orders.work-order-calendar');
    }
}
