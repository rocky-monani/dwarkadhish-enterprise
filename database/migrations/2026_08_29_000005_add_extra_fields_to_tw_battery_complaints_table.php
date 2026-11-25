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
            if (! Schema::hasColumn('battery_complaints', 'location')) {
                $table->string('location', 100)->nullable();
            }
            if (! Schema::hasColumn('battery_complaints', 'online_reg_type')) {
                $table->string('online_reg_type', 50)->nullable();
            }
            if (! Schema::hasColumn('battery_complaints', 'return_date')) {
                $table->date('return_date')->nullable();
            }
            if (! Schema::hasColumn('battery_complaints', 'delivery_mode')) {
                $table->string('delivery_mode', 150)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('battery_complaints', function (Blueprint $table) {
            $cols = array_filter(['location', 'online_reg_type', 'return_date', 'delivery_mode'], function ($c) {
                return Schema::hasColumn('battery_complaints', $c);
            });
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
