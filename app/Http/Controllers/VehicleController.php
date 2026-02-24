<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Vehicle;

class VehicleController extends Controller
{
    public function create(Customer $customer)
    {
        $this->authorizeTenant($customer);
        return view('vehicles.create', compact('customer'));
    }

    public function edit(Customer $customer, Vehicle $vehicle)
    {
        $this->authorizeTenant($customer);
        abort_unless($vehicle->customer_id === $customer->id, 404);
        return view('vehicles.edit', compact('customer', 'vehicle'));
    }

    private function authorizeTenant(Customer $customer): void
    {
        abort_unless($customer->tenant_id === auth()->user()->tenant_id, 403);
    }
}
