<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function customers()
    {
        $tenantId = auth()->user()->tenant_id;

        return Customer::where('tenant_id', $tenantId)
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('first_name', 'like', "%{$this->search}%")
                      ->orWhere('last_name',  'like', "%{$this->search}%")
                      ->orWhere('phone',      'like', "%{$this->search}%")
                      ->orWhere('email',      'like', "%{$this->search}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->withCount('vehicles')
            ->paginate(25);
    }

    public function render()
    {
        return view('livewire.customers.customer-list');
    }
}
