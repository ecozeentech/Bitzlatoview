<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investment_products', function (Blueprint $table) {
            $table->decimal('max_amount', 20, 2)->nullable()->after('min_amount');
            $table->string('risk_level')->default('moderate')->after('apy_pct'); // low, moderate, high
            $table->string('payout_frequency')->default('daily')->after('lock_days'); // daily, weekly
        });
    }

    public function down(): void
    {
        Schema::table('investment_products', function (Blueprint $table) {
            $table->dropColumn(['max_amount', 'risk_level', 'payout_frequency']);
        });
    }
};
