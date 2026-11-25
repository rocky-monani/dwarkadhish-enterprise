<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('battery_complaints', 'product_segment')) {
            Schema::table('battery_complaints', function (Blueprint $table) {
                $table->string('product_segment', 100)->nullable()->after('product_model');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('battery_complaints', 'product_segment')) {
            Schema::table('battery_complaints', function (Blueprint $table) {
                $table->dropColumn('product_segment');
            });
        }
    }
};
