<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CustomerForm extends Component
{
    public ?Customer $customer = null;

    #[Validate('required|string|max:100')]
    public string $first_name = '';

    #[Validate('required|string|max:100')]
    public string $last_name = '';

    #[Validate('nullable|string|max:20')]
    public string $phone = '';

    #[Validate('nullable|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|max:255')]
    public string $address = '';

    #[Validate('nullable|string|max:100')]
    public string $city = '';

    #[Validate('nullable|string|max:2')]
    public string $state = '';

    #[Validate('nullable|string|max:10')]
    public string $zip = '';

    #[Validate('nullable|string|max:1000')]
    public string $notes = '';

    public function mount(?Customer $customer = null): void
    {
        if ($customer?->exists) {
            $this->customer   = $customer;
            $this->first_name = $customer->first_name;
            $this->last_name  = $customer->last_name;
            $this->phone      = $customer->phone ?? '';
            $this->email      = $customer->email ?? '';
            $this->address    = $customer->address ?? '';
            $this->city       = $customer->city ?? '';
            $this->state      = $customer->state ?? '';
            $this->zip        = $customer->zip ?? '';
            $this->notes      = $customer->notes ?? '';
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'tenant_id'  => auth()->user()->tenant_id,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'phone'      => $this->phone ?: null,
            'email'      => $this->email ?: null,
            'address'    => $this->address ?: null,
            'city'       => $this->city ?: null,
            'state'      => $this->state ?: null,
            'zip'        => $this->zip ?: null,
            'notes'      => $this->notes ?: null,
        ];

        if ($this->customer?->exists) {
            $this->customer->update($data);
            session()->flash('success', 'Customer updated.');
            $this->redirect(route('customers.show', $this->customer), navigate: true);
        } else {
            $customer = Customer::create($data);
            session()->flash('success', 'Customer created.');
            $this->redirect(route('customers.show', $customer), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.customers.customer-form');
    }
}
