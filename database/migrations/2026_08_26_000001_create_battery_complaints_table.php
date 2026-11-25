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
        Schema::create('battery_complaints', function (Blueprint $table) {
            $table->id();
            $table->string('battery_serial_number', 100);
            $table->string('complaint_number', 100)->unique();
            $table->string('case_number', 100);
            $table->date('complaint_date');
            $table->string('customer_name', 150);
            $table->string('customer_mobile', 20);
            $table->boolean('is_prorata')->default(false);
            $table->decimal('prorata_percentage', 5, 2)->nullable();
            $table->string('battery_connect_image_path', 255)->nullable();
            $table->timestamp('battery_connect_lookup_at')->nullable();
            $table->json('battery_connect_data')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            // Indexes for ultra-fast search
            $table->index('battery_serial_number');
            $table->index('case_number');
            $table->index('complaint_date');
            $table->index('customer_mobile');
            $table->index('is_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battery_complaints');
    }
};
