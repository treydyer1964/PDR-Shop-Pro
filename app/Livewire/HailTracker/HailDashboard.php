<?php

namespace App\Livewire\HailTracker;

use App\Enums\HailEventStatus;
use App\Models\HailAlertSubscription;
use App\Models\HailEvent;
use App\Models\HailEventWatch;
use App\Models\HailReport;
use App\Models\StormEvent;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class HailDashboard extends Component
{
    #[Url]
    public string $selectedDate = '';

    #[Url]
    public float $filterMinSize = 0.75;

    #[Url]
    public bool $showRadar = false;

    #[Url]
    public bool $showWarnings = false;

    #[Url]
    public bool $showMesh = false;

    // Set after a successful Deploy action — shows a success banner
    public ?int $deployedStormEventId = null;

    public function mount(): void
    {
        if (!$this->selectedDate) {
            $this->selectedDate = now()->toDateString();
        }
        $this->ensureData();
    }

    public function updatedSelectedDate(): void
    {
        $this->ensureData();
    }

    public function updatedFilterMinSize(): void
    {
        // Just triggers re-render — computed properties pick up the change
    }

    public function setToday(): void
    {
        $this->selectedDate = now()->toDateString();
        $this->ensureData();
    }

    public function setYesterday(): void
    {
        $this->selectedDate = now()->subDay()->toDateString();
        $this->ensureData();
    }

    public function toggleRadar(): void
    {
        $this->showRadar = !$this->showRadar;
    }

    public function toggleWarnings(): void
    {
        $this->showWarnings = !$this->showWarnings;
    }

    public function toggleMesh(): void
    {
        $this->showMesh = !$this->showMesh;
    }

    // ── Data fetching ─────────────────────────────────────────────────────────────

    private function ensureData(): void
    {
        if (!$this->selectedDate) return;

        $exists = HailReport::whereDate('report_date', $this->selectedDate)->exists();
        if (!$exists) {
            Artisan::call('hail:fetch-reports', ['--date' => $this->selectedDate]);
        }
    }

    // ── Watch actions ─────────────────────────────────────────────────────────────

    public function watchEvent(int $eventId): void
    {
        HailEventWatch::updateOrCreate(
            ['tenant_id' => auth()->user()->tenant_id, 'hail_event_id' => $eventId],
            ['status' => HailEventStatus::Watching->value, 'created_by' => auth()->id()]
        );
        unset($this->watches);
    }

    public function passEvent(int $eventId): void
    {
        HailEventWatch::updateOrCreate(
            ['tenant_id' => auth()->user()->tenant_id, 'hail_event_id' => $eventId],
            ['status' => HailEventStatus::Passed->value, 'created_by' => auth()->id()]
        );
        unset($this->watches);
    }

    public function activateEvent(int $eventId): void
    {
        $event    = HailEvent::findOrFail($eventId);
        $tenantId = auth()->user()->tenant_id;

        $location = collect([$event->primary_county, $event->primary_state])->filter()->join(', ');
        $name     = 'Hail' . ($location ? ' — ' . $location : '') . ' (' . $event->event_date->format('M j, Y') . ')';

        $stormEvent = StormEvent::create([
            'tenant_id'  => $tenantId,
            'name'       => $name,
            'event_date' => $event->event_date,
            'city'       => $event->primary_county,
            'state'      => $event->primary_state,
            'storm_type' => 'hail',
            'notes'      => "Max hail: {$event->max_size_inches}\" ({$event->sizeLabel()}). {$event->report_count} SPC storm reports.",
        ]);

        HailEventWatch::updateOrCreate(
            ['tenant_id' => $tenantId, 'hail_event_id' => $eventId],
            [
                'status'          => HailEventStatus::Activated->value,
                'storm_event_id'  => $stormEvent->id,
                'created_by'      => auth()->id(),
            ]
        );

        $this->deployedStormEventId = $stormEvent->id;
        unset($this->watches);
    }

    public function dismissDeployBanner(): void
    {
        $this->deployedStormEventId = null;
    }

    // ── Computed properties ───────────────────────────────────────────────────────

    #[Computed]
    public function watches(): array
    {
        return HailEventWatch::where('tenant_id', auth()->user()->tenant_id)
            ->get()
            ->keyBy('hail_event_id')
            ->toArray();
    }

    #[Computed]
    public function reports(): array
    {
        return HailReport::whereDate('report_date', $this->selectedDate)
            ->where('size_inches', '>=', $this->filterMinSize)
            ->orderByDesc('size_inches')
            ->get()
            ->map(fn($r) => [
                'lat'      => $r->lat,
                'lng'      => $r->lng,
                'size'     => $r->size_inches,
                'color'    => $r->sizeColor(),
                'location' => $r->locationLabel(),
                'time'     => $r->report_time ? substr((string) $r->report_time, 0, 5) . ' UTC' : '',
            ])->toArray();
    }

    #[Computed]
    public function events(): array
    {
        $watches = $this->watches;

        return HailEvent::whereDate('event_date', $this->selectedDate)
            ->where('max_size_inches', '>=', $this->filterMinSize)
            ->orderByDesc('max_size_inches')
            ->get()
            ->map(function ($e) use ($watches) {
                $watch = $watches[$e->id] ?? null;

                return [
                    'id'             => $e->id,
                    'lat'            => $e->centroid_lat,
                    'lng'            => $e->centroid_lng,
                    'maxSize'        => $e->max_size_inches,
                    'reportCount'    => $e->report_count,
                    'location'       => $e->locationLabel(),
                    'color'          => $e->sizeColor(),
                    'sizeLabel'      => $e->sizeLabel(),
                    'badgeClass'     => $e->sizeBadgeClasses(),
                    // Watch state
                    'coverageRadiusM' => (int) ($e->coverage_radius_miles * 1609.34),
                    'watchStatus'    => $watch ? $watch['status'] : null,
                    'stormEventId'   => $watch['storm_event_id'] ?? null,
                ];
            })->toArray();
    }

    #[Computed]
    public function subscription(): ?array
    {
        $sub = HailAlertSubscription::where('tenant_id', auth()->user()->tenant_id)->first();

        if (!$sub || !$sub->hasHomeBase()) return null;

        return [
            'lat'         => $sub->home_lat,
            'lng'         => $sub->home_lng,
            'address'     => $sub->home_address,
            'radiusMiles' => $sub->radius_miles,
        ];
    }

    #[Computed]
    public function reportCount(): int
    {
        return count($this->reports);
    }

    #[Computed]
    public function eventCount(): int
    {
        return count($this->events);
    }

    public function render()
    {
        return view('livewire.hail-tracker.hail-dashboard');
    }
}
