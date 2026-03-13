<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class ShopSettings extends Component
{
    use WithFileUploads;

    // Shop info
    public string $name          = '';
    public string $phone         = '';
    public string $email         = '';
    public string $address       = '';
    public string $city          = '';
    public string $state         = '';
    public string $zip           = '';
    public string $remitAddress  = '';

    // Rental invoice note
    public string $rentalInvoiceNote = '';

    // Commission defaults
    public string $advisorPerCarBonus = '';

    // Default staff
    public ?int $defaultRiTechId = null;
    public ?int $defaultPorterId = null;

    // Logo
    public $logo = null; // temporary upload

    public bool $saved = false;

    public function mount(): void
    {
        $tenant = auth()->user()->tenant;

        $this->name              = $tenant->name ?? '';
        $this->phone             = $tenant->phone ?? '';
        $this->email             = $tenant->email ?? '';
        $this->address           = $tenant->address ?? '';
        $this->city              = $tenant->city ?? '';
        $this->state             = $tenant->state ?? '';
        $this->zip               = $tenant->zip ?? '';
        $this->remitAddress        = $tenant->remit_address ?? '';
        $this->rentalInvoiceNote   = $tenant->rental_invoice_note
            ?? "This rental was paid for by {$tenant->name} for the repair of {vehicle}. Please remit reimbursement to " . ($tenant->remit_address ?? $tenant->address ?? '') . '.';
        $this->advisorPerCarBonus = $tenant->advisor_per_car_bonus !== null
            ? number_format((float) $tenant->advisor_per_car_bonus, 2, '.', '')
            : '100.00';
        $this->defaultRiTechId   = $tenant->default_ri_tech_id;
        $this->defaultPorterId   = $tenant->default_porter_id;
    }

    #[Computed]
    public function tenant()
    {
        return auth()->user()->tenant;
    }

    #[Computed]
    public function riTechs()
    {
        return User::where('tenant_id', auth()->user()->tenant_id)
            ->where('active', true)
            ->whereHas('roles', fn($q) => $q->where('id', 'ri_tech'))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function porters()
    {
        return User::where('tenant_id', auth()->user()->tenant_id)
            ->where('active', true)
            ->whereHas('roles', fn($q) => $q->where('id', 'porter'))
            ->orderBy('name')
            ->get();
    }

    public function save(): void
    {
        $this->validate([
            'name'              => 'required|string|max:255',
            'phone'             => 'nullable|string|max:30',
            'email'             => 'nullable|email|max:255',
            'address'           => 'nullable|string|max:255',
            'city'              => 'nullable|string|max:100',
            'state'             => 'nullable|string|max:50',
            'zip'               => 'nullable|string|max:20',
            'remitAddress'        => 'nullable|string|max:500',
            'rentalInvoiceNote'   => 'nullable|string|max:2000',
            'advisorPerCarBonus'  => 'nullable|numeric|min:0',
            'defaultRiTechId'   => 'nullable|integer',
            'defaultPorterId'   => 'nullable|integer',
            'logo'              => 'nullable|image|max:4096',
        ]);

        $data = [
            'name'                  => $this->name,
            'slug'                  => Str::slug($this->name),
            'phone'                 => $this->phone ?: null,
            'email'                 => $this->email ?: null,
            'address'               => $this->address ?: null,
            'city'                  => $this->city ?: null,
            'state'                 => $this->state ?: null,
            'zip'                   => $this->zip ?: null,
            'remit_address'         => $this->remitAddress ?: null,
            'rental_invoice_note'   => $this->rentalInvoiceNote ?: null,
            'advisor_per_car_bonus' => $this->advisorPerCarBonus !== '' ? (float) $this->advisorPerCarBonus : null,
            'default_ri_tech_id'    => $this->defaultRiTechId ?: null,
            'default_porter_id'     => $this->defaultPorterId ?: null,
        ];

        // Handle logo upload
        if ($this->logo) {
            // Delete old logo
            if ($this->tenant->logo_path) {
                Storage::disk('public')->delete($this->tenant->logo_path);
            }

            $path = $this->logo->store('logos', 'public');
            $data['logo_path'] = $path;
            $this->logo = null;
        }

        $this->tenant->update($data);

        $this->saved = true;
        $this->dispatch('saved');
    }

    public function render()
    {
        return view('livewire.settings.shop-settings');
    }
}
