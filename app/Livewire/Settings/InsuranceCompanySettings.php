<?php

namespace App\Livewire\Settings;

use App\Models\InsuranceCompany;
use Livewire\Attributes\Computed;
use Livewire\Component;

class InsuranceCompanySettings extends Component
{
    public bool   $showAddForm  = false;
    public string $addName      = '';
    public string $addShortName = '';
    public string $addPhone     = '';

    public ?int   $editingId    = null;
    public string $editName     = '';
    public string $editShortName = '';
    public string $editPhone    = '';

    #[Computed]
    public function companies()
    {
        return InsuranceCompany::orderBy('name')->get();
    }

    public function startEdit(int $id): void
    {
        $co = InsuranceCompany::findOrFail($id);
        $this->editingId    = $id;
        $this->editName     = $co->name;
        $this->editShortName = $co->short_name ?? '';
        $this->editPhone    = $co->phone ?? '';
        $this->showAddForm  = false;
    }

    public function saveEdit(): void
    {
        $this->validate(['editName' => 'required|string|max:200']);

        InsuranceCompany::findOrFail($this->editingId)->update([
            'name'       => $this->editName,
            'short_name' => $this->editShortName ?: null,
            'phone'      => $this->editPhone ?: null,
        ]);

        $this->editingId = null;
        unset($this->companies);
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function toggleActive(int $id): void
    {
        $co = InsuranceCompany::findOrFail($id);
        $co->update(['is_active' => ! $co->is_active]);
        unset($this->companies);
    }

    public function addCompany(): void
    {
        $this->validate(['addName' => 'required|string|max:200']);

        InsuranceCompany::create([
            'name'       => $this->addName,
            'short_name' => $this->addShortName ?: null,
            'phone'      => $this->addPhone ?: null,
            'is_active'  => true,
        ]);

        $this->addName      = '';
        $this->addShortName = '';
        $this->addPhone     = '';
        $this->showAddForm  = false;
        unset($this->companies);
    }

    public function render()
    {
        return view('livewire.settings.insurance-company-settings');
    }
}
