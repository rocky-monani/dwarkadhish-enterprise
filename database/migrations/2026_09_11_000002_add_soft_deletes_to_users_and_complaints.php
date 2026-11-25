<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('battery_complaints', 'deleted_at')) {
            Schema::table('battery_complaints', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (!Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Migrate any legacy is_deleted = true rows to deleted_at = now()
        if (Schema::hasColumn('battery_complaints', 'is_deleted')) {
            DB::table('battery_complaints')
                ->where('is_deleted', true)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => now()]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('battery_complaints', 'deleted_at')) {
            Schema::table('battery_complaints', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
