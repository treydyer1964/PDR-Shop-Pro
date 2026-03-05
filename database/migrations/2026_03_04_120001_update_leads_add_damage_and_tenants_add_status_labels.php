<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('first_name')->nullable()->change();
            $table->string('damage_level', 20)->nullable()->after('notes');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->json('lead_status_labels')->nullable()->after('advisor_per_car_bonus');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('first_name')->nullable(false)->change();
            $table->dropColumn('damage_level');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('lead_status_labels');
        });
    }
};
