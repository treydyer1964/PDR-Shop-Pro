<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->date('birthdate')->nullable()->after('zip');
            $table->string('drivers_license', 50)->nullable()->after('birthdate');
            $table->char('drivers_license_state', 2)->nullable()->after('drivers_license');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['birthdate', 'drivers_license', 'drivers_license_state']);
        });
    }
};
