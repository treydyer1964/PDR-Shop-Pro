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
        Schema::table('work_orders', function (Blueprint $table) {
            $table->boolean('needs_rental')->default(false)->after('has_rental_coverage');
            $table->decimal('insurance_daily_coverage', 8, 2)->nullable()->after('needs_rental');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn(['needs_rental', 'insurance_daily_coverage']);
        });
    }
};
