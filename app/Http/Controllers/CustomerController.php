<?php

namespace App\Http\Controllers;

use App\Models\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        return view('customers.index');
    }

    public function create()
    {
        return view('customers.create');
    }

    public function show(Customer $customer)
    {
        $this->authorizeTenant($customer);
        $customer->load('vehicles');
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $this->authorizeTenant($customer);
        return view('customers.edit', compact('customer'));
    }

    private function authorizeTenant(Customer $customer): void
    {
        abort_unless($customer->tenant_id === auth()->user()->tenant_id, 403);
    }
}
