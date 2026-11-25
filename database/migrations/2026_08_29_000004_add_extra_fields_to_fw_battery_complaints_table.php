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
        Schema::table('fw_battery_complaints', function (Blueprint $table) {
            $table->string('location', 100)->nullable()->after('customer_mobile');
            $table->string('online_reg_type', 50)->nullable()->after('is_digital_warranty');
            $table->date('return_date')->nullable()->after('closing_date');
            $table->string('delivery_mode', 150)->nullable()->after('return_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fw_battery_complaints', function (Blueprint $table) {
            $table->dropColumn(['location', 'online_reg_type', 'return_date', 'delivery_mode']);
        });
    }
};
