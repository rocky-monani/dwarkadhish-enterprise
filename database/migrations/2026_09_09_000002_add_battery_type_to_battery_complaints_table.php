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
            if (! Schema::hasColumn('battery_complaints', 'battery_type')) {
                $table->string('battery_type', 10)->default('TW')->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('battery_complaints', function (Blueprint $table) {
            if (Schema::hasColumn('battery_complaints', 'battery_type')) {
                $table->dropColumn('battery_type');
            }
        });
    }
};
