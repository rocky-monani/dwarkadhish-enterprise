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
        Schema::table('battery_complaints', function (Blueprint $table) {
            $table->date('purchase_date')->nullable()->after('complaint_date');
            $table->string('vehicle_name', 100)->nullable()->after('customer_mobile');
            $table->string('vehicle_number', 50)->nullable()->after('vehicle_name');
            $table->boolean('is_digital_warranty')->default(false)->after('vehicle_number');
            $table->boolean('is_service_battery_given')->default(false)->after('is_digital_warranty');
            $table->string('service_battery_number', 100)->nullable()->after('is_service_battery_given');

            // Closing / Settlement Workflow Fields
            $table->string('complaint_status', 30)->default('Open')->after('is_deleted'); // Open, Closed, Rejected
            $table->string('final_decision', 100)->nullable()->after('complaint_status'); // Free Replacement, Pro-Rata, Recharged OK, Rejected
            $table->date('decision_date')->nullable()->after('final_decision');
            $table->string('new_battery_number', 100)->nullable()->after('decision_date');
            $table->date('closing_date')->nullable()->after('new_battery_number');
            $table->unsignedBigInteger('closed_by')->nullable()->after('closing_date');

            // Indexes
            $table->index('vehicle_number');
            $table->index('complaint_status');
            $table->index('purchase_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('battery_complaints', function (Blueprint $table) {
            $table->dropIndex(['vehicle_number']);
            $table->dropIndex(['complaint_status']);
            $table->dropIndex(['purchase_date']);

            $table->dropColumn([
                'purchase_date',
                'vehicle_name',
                'vehicle_number',
                'is_digital_warranty',
                'is_service_battery_given',
                'service_battery_number',
                'complaint_status',
                'final_decision',
                'decision_date',
                'new_battery_number',
                'closing_date',
                'closed_by',
            ]);
        });
    }
};
