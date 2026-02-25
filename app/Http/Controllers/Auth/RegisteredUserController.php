<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AppointmentType;
use App\Models\ExpenseCategory;
use App\Models\Tenant;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'shop_name' => ['required', 'string', 'max:255'],
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password'  => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $tenant = Tenant::createWithOwner(
            tenantData: ['name' => $request->shop_name],
            ownerData: [
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => $request->password, // Tenant::createWithOwner passes to users()->create() which hashes via cast
            ]
        );

        ExpenseCategory::seedForTenant($tenant->id);
        AppointmentType::seedForTenant($tenant->id);

        $user = $tenant->users()->where('email', $request->email)->first();

        event(new Registered($user));
        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
