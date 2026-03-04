<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->unsignedBigInteger('default_ri_tech_id')->nullable()->after('advisor_per_car_bonus');
            $table->unsignedBigInteger('default_porter_id')->nullable()->after('default_ri_tech_id');
            $table->foreign('default_ri_tech_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('default_porter_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['default_ri_tech_id']);
            $table->dropForeign(['default_porter_id']);
            $table->dropColumn(['default_ri_tech_id', 'default_porter_id']);
        });
    }
};
