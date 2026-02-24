<?php

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed all roles
        foreach (RoleEnum::cases() as $role) {
            Role::firstOrCreate(
                ['name' => $role->value],
                ['label' => $role->label()]
            );
        }

        // Dev tenant: Go Pro Auto Hail Repair
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'go-pro-auto'],
            [
                'name'               => 'Go Pro Auto Hail Repair',
                'phone'              => '325-555-0100',
                'email'              => 'admin@gopro.com',
                'city'               => 'Abilene',
                'state'              => 'TX',
                'rental_daily_rate'  => 35.00,
                'advisor_per_car_bonus' => 100.00,
                'active'             => true,
            ]
        );

        // Default location
        $tenant->locations()->firstOrCreate(
            ['name' => 'Abilene Main'],
            ['city' => 'Abilene', 'state' => 'TX', 'active' => true]
        );

        // Owner account (Trey)
        $owner = $tenant->users()->firstOrCreate(
            ['email' => 'trey@gopro.com'],
            [
                'name'            => 'Trey Dyer',
                'password'        => Hash::make('password'),
                'active'          => true,
                'commission_rate' => 50.00,
            ]
        );
        $owner->roles()->syncWithoutDetaching(['owner']);

        // Sample PDR Tech
        $tech = $tenant->users()->firstOrCreate(
            ['email' => 'tech@gopro.com'],
            [
                'name'            => 'Sample Tech',
                'password'        => Hash::make('password'),
                'active'          => true,
                'commission_rate' => 50.00,
            ]
        );
        $tech->roles()->syncWithoutDetaching(['pdr_tech']);

        // Sample Sales Advisor
        $advisor = $tenant->users()->firstOrCreate(
            ['email' => 'advisor@gopro.com'],
            [
                'name'                        => 'Sample Advisor',
                'password'                    => Hash::make('password'),
                'active'                      => true,
                'commission_rate'             => 18.00,
                'subject_to_manager_override' => true,
            ]
        );
        $advisor->roles()->syncWithoutDetaching(['sales_advisor']);
    }
}
