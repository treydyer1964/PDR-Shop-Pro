<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Vehicle;
use App\Services\VinService;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * POST /vin/extract
     * Accepts a base64-encoded image, returns the VIN extracted by OpenAI Vision.
     */
    public function extractVin(Request $request, VinService $vinService)
    {
        $request->validate([
            'image'     => 'required|string',
            'mime_type' => 'sometimes|string',
        ]);

        $vin = $vinService->extractFromImage(
            $request->input('image'),
            $request->input('mime_type', 'image/jpeg')
        );

        if (!$vin) {
            return response()->json(['error' => 'No VIN found in image'], 422);
        }

        return response()->json([
            'vin'     => $vin,
            'partial' => strlen($vin) !== 17,
        ]);
    }

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
