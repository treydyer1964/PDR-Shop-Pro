<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->date('invoice_date')->nullable()->after('invoice_total');
            $table->boolean('is_closed')->default(false)->after('commissions_locked_at');
            $table->timestamp('closed_at')->nullable()->after('is_closed');
            $table->foreignId('closed_by')->nullable()->constrained('users')->after('closed_at');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropForeign(['closed_by']);
            $table->dropColumn(['invoice_date', 'is_closed', 'closed_at', 'closed_by']);
        });
    }
};
