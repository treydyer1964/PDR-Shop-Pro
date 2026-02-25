<?php

namespace Database\Seeders;

use App\Models\AppointmentType;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class AppointmentTypeSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::all()->each(fn ($tenant) => AppointmentType::seedForTenant($tenant->id));
    }
}
