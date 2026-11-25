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
        Schema::create('fw_battery_complaints', function (Blueprint $table) {
            $table->id();
            $table->string('battery_serial_number', 100);
            $table->string('product_model', 100)->nullable();
            $table->string('complaint_number', 100)->unique();
            $table->string('case_number', 100)->nullable();
            $table->date('complaint_date');
            $table->string('customer_name', 150);
            $table->string('customer_mobile', 20)->nullable();
            $table->date('purchase_date')->nullable();
            $table->string('vehicle_name', 100)->nullable();
            $table->string('vehicle_number', 50)->nullable();
            $table->boolean('is_digital_warranty')->default(false);
            $table->boolean('is_service_battery_given')->default(false);
            $table->string('service_battery_number', 100)->nullable();
            $table->boolean('is_prorata')->default(false);
            $table->decimal('prorata_percentage', 5, 2)->nullable();
            $table->string('battery_connect_image_path', 255)->nullable();
            $table->timestamp('battery_connect_lookup_at')->nullable();
            $table->json('battery_connect_data')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('is_deleted')->default(false);

            // Closing / Settlement Workflow Fields
            $table->string('complaint_status', 30)->default('Open');
            $table->string('final_decision', 100)->nullable();
            $table->date('decision_date')->nullable();
            $table->string('new_battery_number', 100)->nullable();
            $table->date('closing_date')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('battery_serial_number');
            $table->index('complaint_number');
            $table->index('case_number');
            $table->index('complaint_date');
            $table->index('customer_mobile');
            $table->index('vehicle_number');
            $table->index('complaint_status');
            $table->index('purchase_date');
            $table->index('is_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fw_battery_complaints');
    }
};
