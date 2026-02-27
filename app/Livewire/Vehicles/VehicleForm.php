<?php

namespace App\Livewire\Vehicles;

use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\VehicleColor;
use App\Services\VinService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class VehicleForm extends Component
{
    use WithFileUploads;

    public Customer $customer;
    public ?Vehicle $vehicle = null;

    // VIN entry
    #[Validate('nullable|string|size:17|regex:/^[A-HJ-NPR-Z0-9]{17}$/i')]
    public string $vin = '';

    public bool $vinDecoding = false;
    public bool $vinDecoded  = false;
    public ?string $vinError = null;

    // OpenAI image upload fallback
    public $vinPhoto = null;
    public bool $vinPhotoProcessing = false;

    // Decoded vehicle fields
    #[Validate('nullable|integer|min:1900|max:2100')]
    public ?int $year = null;

    #[Validate('nullable|string|max:50')]
    public string $make = '';

    #[Validate('nullable|string|max:50')]
    public string $model = '';

    #[Validate('nullable|string|max:100')]
    public string $trim = '';

    #[Validate('nullable|string|max:100')]
    public string $body_style = '';

    #[Validate('nullable|string|max:50')]
    public string $drive_type = '';

    #[Validate('nullable|string|max:50')]
    public string $engine = '';

    #[Validate('nullable|string|max:50')]
    public string $color = '';

    #[Validate('nullable|string|max:20')]
    public string $plate = '';

    #[Validate('nullable|string|max:500')]
    public string $notes = '';

    public function mount(Customer $customer, ?Vehicle $vehicle = null): void
    {
        $this->customer = $customer;

        if ($vehicle?->exists) {
            $this->vehicle    = $vehicle;
            $this->vin        = $vehicle->vin ?? '';
            $this->year       = $vehicle->year;
            $this->make       = $vehicle->make ?? '';
            $this->model      = $vehicle->model ?? '';
            $this->trim       = $vehicle->trim ?? '';
            $this->body_style = $vehicle->body_style ?? '';
            $this->drive_type = $vehicle->drive_type ?? '';
            $this->engine     = $vehicle->engine ?? '';
            $this->color      = $vehicle->color ?? '';
            $this->plate      = $vehicle->plate ?? '';
            $this->notes      = $vehicle->notes ?? '';
            $this->vinDecoded = (bool) $vehicle->year;
        }
    }

    /**
     * Called from JS via @this.decodeVin() when barcode is scanned or VIN typed.
     */
    public function decodeVin(): void
    {
        $this->vinError   = null;
        $this->vinDecoded = false;
        $this->vinDecoding = true;

        $vin = strtoupper(trim($this->vin));

        if (strlen($vin) !== 17) {
            $this->vinError   = 'VIN must be exactly 17 characters.';
            $this->vinDecoding = false;
            return;
        }

        /** @var VinService $vinService */
        $vinService = app(VinService::class);

        if (!$vinService->isValidVin($vin)) {
            $this->vinError   = 'This does not appear to be a valid VIN. Please check the number and try again.';
            $this->vinDecoding = false;
            return;
        }

        $decoded = $vinService->decode($vin);

        if (!$decoded) {
            $this->vinError   = 'Could not decode this VIN. Please enter vehicle details manually.';
            $this->vinDecoding = false;
            return;
        }

        $this->vin        = $decoded['vin'];
        $this->year       = $decoded['year'];
        $this->make       = $decoded['make'] ?? '';
        $this->model      = $decoded['model'] ?? '';
        $this->trim       = $decoded['trim'] ?? '';
        $this->body_style = $decoded['body_style'] ?? '';
        $this->drive_type = $decoded['drive_type'] ?? '';
        $this->engine     = $decoded['engine'] ?? '';
        $this->vinDecoded  = true;
        $this->vinDecoding = false;
    }

    /**
     * Called when user uploads a VIN photo (OpenAI Vision fallback).
     */
    public function updatedVinPhoto(): void
    {
        $this->vinPhotoProcessing = true;
        $this->vinError = null;

        try {
            $path    = $this->vinPhoto->getRealPath();
            $mime    = $this->vinPhoto->getMimeType();
            $base64  = base64_encode(file_get_contents($path));

            /** @var VinService $vinService */
            $vinService = app(VinService::class);
            $vin = $vinService->extractFromImage($base64, $mime);

            if ($vin) {
                $this->vin = $vin;
                $this->vinPhotoProcessing = false;
                $this->decodeVin();
            } else {
                $this->vinError = 'Could not find a VIN in the photo. Try again with better lighting, or enter manually.';
            }
        } catch (\Throwable $e) {
            $this->vinError = 'Photo processing failed. Please enter the VIN manually.';
        }

        $this->vinPhotoProcessing = false;
        $this->vinPhoto = null;
    }

    /**
     * Receives VIN from the ZXing JS barcode scanner via browser event.
     */
    public function receiveScanResult(string $vin): void
    {
        $this->vin = strtoupper(trim($vin));
        $this->decodeVin();
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'tenant_id'   => auth()->user()->tenant_id,
            'customer_id' => $this->customer->id,
            'vin'         => $this->vin ?: null,
            'year'        => $this->year,
            'make'        => $this->make ?: null,
            'model'       => $this->model ?: null,
            'trim'        => $this->trim ?: null,
            'body_style'  => $this->body_style ?: null,
            'drive_type'  => $this->drive_type ?: null,
            'engine'      => $this->engine ?: null,
            'color'       => $this->color ?: null,
            'plate'       => $this->plate ?: null,
            'notes'       => $this->notes ?: null,
        ];

        if ($this->vehicle?->exists) {
            $this->vehicle->update($data);
            session()->flash('success', 'Vehicle updated.');
            $this->redirect(route('customers.show', $this->customer), navigate: true);
        } else {
            Vehicle::create($data);
            session()->flash('success', 'Vehicle added.');
            $this->redirect(route('customers.show', $this->customer), navigate: true);
        }
    }

    #[Computed]
    public function vehicleColors(): array
    {
        return VehicleColor::active()->pluck('name')->toArray();
    }

    public function render()
    {
        return view('livewire.vehicles.vehicle-form');
    }
}
