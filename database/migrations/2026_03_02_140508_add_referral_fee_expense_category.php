<?php

use App\Models\ExpenseCategory;
use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Re-run seedForTenant for all tenants — uses firstOrCreate,
        // so existing categories are untouched; only the new one is inserted.
        Tenant::all()->each(fn($tenant) => ExpenseCategory::seedForTenant($tenant->id));
    }

    public function down(): void
    {
        ExpenseCategory::where('slug', ExpenseCategory::SLUG_REFERRAL_FEE)->delete();
    }
};
