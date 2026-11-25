<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('battery_complaints', function (Blueprint $table) {
            if (! Schema::hasColumn('battery_complaints', 'product_model')) {
                $table->string('product_model', 100)->nullable()->after('battery_serial_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('battery_complaints', function (Blueprint $table) {
            if (Schema::hasColumn('battery_complaints', 'product_model')) {
                $table->dropColumn('product_model');
            }
        });
    }
};
