<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->decimal('net_amount', 36, 18)->default(0)->after('fee');
        });

        Schema::table('kyc_submissions', function (Blueprint $table) {
            $table->boolean('manually_verified')->default(false)->after('is_sanctioned');
            $table->text('manual_approval_reason')->nullable()->after('manually_verified');
        });
    }

    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn('net_amount');
        });

        Schema::table('kyc_submissions', function (Blueprint $table) {
            $table->dropColumn(['manually_verified', 'manual_approval_reason']);
        });
    }
};
