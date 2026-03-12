<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Make work_order_id nullable so lead follow-up appointments don't need a WO
            $table->foreignId('work_order_id')->nullable()->change();
            // Link appointment to a lead (for follow-up/sales appointments)
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete()->after('work_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lead_id');
            $table->foreignId('work_order_id')->nullable(false)->change();
        });
    }
};
