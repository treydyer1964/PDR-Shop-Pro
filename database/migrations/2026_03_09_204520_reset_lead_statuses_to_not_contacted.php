<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Reset all legacy status values to the new 'not_contacted' status.
        DB::table('leads')->update(['status' => 'not_contacted']);

        // Clear stale lead_status_labels JSON on tenants (keys changed).
        DB::table('tenants')->update(['lead_status_labels' => null]);
    }

    public function down(): void
    {
        // No meaningful rollback — original status values not preserved.
    }
};
