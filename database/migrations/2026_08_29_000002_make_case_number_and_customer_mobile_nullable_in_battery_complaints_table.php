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
            $table->string('case_number', 100)->nullable()->change();
            $table->string('customer_mobile', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('battery_complaints', function (Blueprint $table) {
            $table->string('case_number', 100)->nullable(false)->change();
            $table->string('customer_mobile', 20)->nullable(false)->change();
        });
    }
};
