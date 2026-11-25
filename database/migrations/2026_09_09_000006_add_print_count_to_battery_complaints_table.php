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
            if (! Schema::hasColumn('battery_complaints', 'print_count')) {
                $table->integer('print_count')->default(0)->after('closed_by');
                $table->index('print_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('battery_complaints', function (Blueprint $table) {
            if (Schema::hasColumn('battery_complaints', 'print_count')) {
                $table->dropColumn('print_count');
            }
        });
    }
};
