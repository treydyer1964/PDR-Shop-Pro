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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('phone')->nullable()->after('name');
            $table->boolean('active')->default(true)->after('remember_token');
            // Commission-related fields (set per-user by owner)
            $table->decimal('commission_rate', 5, 2)->default(0)->after('active'); // e.g. 50.00 = 50%
            $table->decimal('sales_manager_override_rate', 5, 2)->default(0)->after('commission_rate');
            $table->boolean('subject_to_manager_override')->default(false)->after('sales_manager_override_rate');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn([
                'tenant_id', 'phone', 'active',
                'commission_rate', 'sales_manager_override_rate', 'subject_to_manager_override',
            ]);
        });
    }
};
