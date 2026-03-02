<?php

namespace App\Livewire\Rentals;

use App\Models\RentalVehicle;
use App\Services\VinService;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class RentalVehicleList extends Component
{
    use WithFileUploads;

    // Add / edit form
    public bool   $showForm    = false;
    public ?int   $editingId   = null;
    public string $name        = '';
    public string $vin         = '';
    public string $year        = '';
    public string $make        = '';
    public string $model       = '';
    public string $color       = '';
    public string $dailyCost   = '';
    public string $notes       = '';

    // New fields
    public string $plateNumber  = '';
    public string $currentOdometer    = '';
    public string $serviceIntervalMiles      = '3000';
    public string $serviceAlertThresholdMiles = '500';

    // VIN scanner state
    public bool    $vinDecoding       = false;
    public bool    $vinDecoded        = false;
    public ?string $vinError          = null;
    public $vinPhoto                  = null;
    public bool    $vinPhotoProcessing = false;

    // Mark serviced
    public ?int   $servicingId        = null;
    public string $servicedOdometer   = '';
    public bool   $showServiceForm    = false;

    #[Computed]
    public function vehicles()
    {
        return RentalVehicle::forTenant(auth()->user()->tenant_id)
            ->orderBy('active', 'desc')
            ->orderBy('name')
            ->get();
    }

    public function openCreate(): void
    {
        $this->reset([
            'editingId', 'name', 'vin', 'year', 'make', 'model', 'color', 'dailyCost', 'notes',
            'plateNumber', 'currentOdometer', 'serviceIntervalMiles', 'serviceAlertThresholdMiles',
            'vinDecoded', 'vinDecoding', 'vinError',
        ]);
        $this->serviceIntervalMiles       = '3000';
        $this->serviceAlertThresholdMiles = '500';
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $v = RentalVehicle::findOrFail($id);
        abort_unless($v->tenant_id === auth()->user()->tenant_id, 403);

        $this->editingId              = $id;
        $this->name                   = $v->name;
        $this->vin                    = $v->vin ?? '';
        $this->year                   = $v->year ? (string) $v->year : '';
        $this->make                   = $v->make ?? '';
        $this->model                  = $v->model ?? '';
        $this->color                  = $v->color ?? '';
        $this->dailyCost              = number_format((float) $v->internal_daily_cost, 2, '.', '');
        $this->notes                  = $v->notes ?? '';
        $this->plateNumber            = $v->plate_number ?? '';
        $this->currentOdometer        = $v->current_odometer !== null ? (string) $v->current_odometer : '';
        $this->serviceIntervalMiles   = (string) ($v->service_interval_miles ?? 3000);
        $this->serviceAlertThresholdMiles = (string) ($v->service_alert_threshold_miles ?? 500);
        $this->vinDecoded             = (bool) $v->year;
        $this->showForm               = true;
    }

    // ── VIN scanner methods (same pattern as VehicleForm) ──────────────────────

    public function decodeVin(): void
    {
        $this->vinError   = null;
        $this->vinDecoded = false;
        $this->vinDecoding = true;

        $vin = strtoupper(trim($this->vin));

        if (strlen($vin) !== 17) {
            $this->vinError    = 'VIN must be exactly 17 characters.';
            $this->vinDecoding = false;
            return;
        }

        $vinService = app(VinService::class);

        if (! $vinService->isValidVin($vin)) {
            $this->vinError    = 'This does not appear to be a valid VIN.';
            $this->vinDecoding = false;
            return;
        }

        $decoded = $vinService->decode($vin);

        if (! $decoded) {
            $this->vinError    = 'Could not decode this VIN. Enter vehicle details manually.';
            $this->vinDecoding = false;
            return;
        }

        $this->vin        = $decoded['vin'];
        $this->year       = (string) $decoded['year'];
        $this->make       = $decoded['make'] ?? '';
        $this->model      = $decoded['model'] ?? '';
        $this->vinDecoded  = true;
        $this->vinDecoding = false;
    }

    public function receiveScanResult(string $vin): void
    {
        $this->vin = strtoupper(trim($vin));
        $this->decodeVin();
    }

    public function updatedVinPhoto(): void
    {
        $this->vinPhotoProcessing = true;
        $this->vinError = null;

        try {
            $path   = $this->vinPhoto->getRealPath();
            $mime   = $this->vinPhoto->getMimeType();
            $base64 = base64_encode(file_get_contents($path));

            $vinService = app(VinService::class);
            $vin = $vinService->extractFromImage($base64, $mime);

            if ($vin) {
                $this->vin = $vin;
                $this->vinPhotoProcessing = false;
                $this->decodeVin();
            } else {
                $this->vinError = 'Could not find a VIN in the photo. Try again or enter manually.';
            }
        } catch (\Throwable $e) {
            $this->vinError = 'Photo processing failed. Please enter the VIN manually.';
        }

        $this->vinPhotoProcessing = false;
        $this->vinPhoto = null;
    }

    // ── Save ───────────────────────────────────────────────────────────────────

    public function save(): void
    {
        $data = $this->validate([
            'name'                          => 'required|string|max:100',
            'vin'                           => 'nullable|string|size:17',
            'year'                          => 'nullable|integer|min:1900|max:2100',
            'make'                          => 'nullable|string|max:50',
            'model'                         => 'nullable|string|max:50',
            'color'                         => 'nullable|string|max:50',
            'dailyCost'                     => 'required|numeric|min:0',
            'notes'                         => 'nullable|string|max:1000',
            'plateNumber'                   => 'nullable|string|max:20',
            'currentOdometer'               => 'nullable|integer|min:0',
            'serviceIntervalMiles'          => 'required|integer|min:100',
            'serviceAlertThresholdMiles'    => 'required|integer|min:0',
        ], [
            'vin.size'               => 'VIN must be exactly 17 characters.',
            'dailyCost.required'     => 'Enter the internal daily cost.',
            'dailyCost.numeric'      => 'Daily cost must be a number.',
        ]);

        $fields = [
            'name'                          => $data['name'],
            'vin'                           => $data['vin'] ? strtoupper($data['vin']) : null,
            'year'                          => $data['year'] ?: null,
            'make'                          => $data['make'] ?: null,
            'model'                         => $data['model'] ?: null,
            'color'                         => $data['color'] ?: null,
            'internal_daily_cost'           => (float) $data['dailyCost'],
            'notes'                         => $data['notes'] ?: null,
            'plate_number'                  => $data['plateNumber'] ?: null,
            'current_odometer'              => $data['currentOdometer'] !== '' && $data['currentOdometer'] !== null
                                                ? (int) $data['currentOdometer'] : null,
            'service_interval_miles'        => (int) $data['serviceIntervalMiles'],
            'service_alert_threshold_miles' => (int) $data['serviceAlertThresholdMiles'],
        ];

        if ($this->editingId) {
            $v = RentalVehicle::findOrFail($this->editingId);
            abort_unless($v->tenant_id === auth()->user()->tenant_id, 403);
            $v->update($fields);
        } else {
            RentalVehicle::create(array_merge($fields, [
                'tenant_id' => auth()->user()->tenant_id,
                'active'    => true,
            ]));
        }

        $this->showForm = false;
        unset($this->vehicles);
    }

    public function toggleActive(int $id): void
    {
        $v = RentalVehicle::findOrFail($id);
        abort_unless($v->tenant_id === auth()->user()->tenant_id, 403);
        $v->update(['active' => ! $v->active]);
        unset($this->vehicles);
    }

    public function cancel(): void
    {
        $this->showForm  = false;
        $this->editingId = null;
    }

    // ── Mark Serviced ──────────────────────────────────────────────────────────

    public function openServiceForm(int $id): void
    {
        $v = RentalVehicle::findOrFail($id);
        abort_unless($v->tenant_id === auth()->user()->tenant_id, 403);

        $this->servicingId      = $id;
        $this->servicedOdometer = $v->current_odometer !== null ? (string) $v->current_odometer : '';
        $this->showServiceForm  = true;
    }

    public function markServiced(): void
    {
        $this->validate([
            'servicedOdometer' => 'required|integer|min:0',
        ], [
            'servicedOdometer.required' => 'Enter the current odometer reading at the time of service.',
        ]);

        $v = RentalVehicle::findOrFail($this->servicingId);
        abort_unless($v->tenant_id === auth()->user()->tenant_id, 403);

        $v->update([
            'last_service_odometer' => (int) $this->servicedOdometer,
            'current_odometer'      => max((int) $this->servicedOdometer, $v->current_odometer ?? 0),
        ]);

        $this->showServiceForm = false;
        $this->servicingId     = null;
        $this->reset(['servicedOdometer']);
        unset($this->vehicles);
    }

    public function cancelService(): void
    {
        $this->showServiceForm = false;
        $this->servicingId     = null;
    }

    public function render()
    {
        return view('livewire.rentals.rental-vehicle-list');
    }
}
