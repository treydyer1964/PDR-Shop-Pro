<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Seed standard expense categories for all existing tenants
        Tenant::all()->each(fn($tenant) => ExpenseCategory::seedForTenant($tenant->id));
    }
}
