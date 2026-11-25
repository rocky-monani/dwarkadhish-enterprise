<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('battery_complaints', function (Blueprint $table) {
            $table->index(['deleted_at', 'complaint_status', 'complaint_date', 'battery_type'], 'idx_bc_status_date_type');
            $table->index(['deleted_at', 'complaint_date', 'battery_type'], 'idx_bc_date_type');
            $table->index(['deleted_at', 'battery_serial_number'], 'idx_bc_serial');
            $table->index(['deleted_at', 'complaint_number'], 'idx_bc_complaint_no');
        });
    }

    public function down(): void
    {
        Schema::table('battery_complaints', function (Blueprint $table) {
            $table->dropIndex('idx_bc_status_date_type');
            $table->dropIndex('idx_bc_date_type');
            $table->dropIndex('idx_bc_serial');
            $table->dropIndex('idx_bc_complaint_no');
        });
    }
};
