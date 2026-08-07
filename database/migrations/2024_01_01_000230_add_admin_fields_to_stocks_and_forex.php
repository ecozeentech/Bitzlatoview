<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_instruments', function (Blueprint $table) {
            $table->string('currency', 8)->default('USD')->after('exchange');
            $table->boolean('is_active')->default(true)->after('change_pct');
        });

        Schema::table('forex_pairs', function (Blueprint $table) {
            $table->unsignedInteger('leverage_max')->default(100)->after('spread_pips');
            $table->boolean('is_active')->default(true)->after('leverage_max');
        });
    }

    public function down(): void
    {
        Schema::table('stock_instruments', function (Blueprint $table) {
            $table->dropColumn(['currency', 'is_active']);
        });

        Schema::table('forex_pairs', function (Blueprint $table) {
            $table->dropColumn(['leverage_max', 'is_active']);
        });
    }
};
