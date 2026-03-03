<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('storm_event_id')->nullable()->after('referred_by');
            $table->foreign('storm_event_id')->references('id')->on('storm_events')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropForeign(['storm_event_id']);
            $table->dropColumn('storm_event_id');
        });
    }
};
