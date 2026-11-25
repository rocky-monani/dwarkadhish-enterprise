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
            $table->string('excel_sheet')->nullable()->after('closed_by');
            $table->integer('excel_row_number')->nullable()->after('excel_sheet');
        });

        Schema::table('fw_battery_complaints', function (Blueprint $table) {
            $table->string('excel_sheet')->nullable()->after('closed_by');
            $table->integer('excel_row_number')->nullable()->after('excel_sheet');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('battery_complaints', function (Blueprint $table) {
            $table->dropColumn(['excel_sheet', 'excel_row_number']);
        });

        Schema::table('fw_battery_complaints', function (Blueprint $table) {
            $table->dropColumn(['excel_sheet', 'excel_row_number']);
        });
    }
};
