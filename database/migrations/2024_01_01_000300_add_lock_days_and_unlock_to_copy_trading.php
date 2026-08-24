<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trader_profiles', function (Blueprint $table) {
            $table->unsignedInteger('lock_days')->default(30)->after('min_copy_amount');
        });

        Schema::table('copy_allocations', function (Blueprint $table) {
            $table->timestamp('unlocks_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('trader_profiles', function (Blueprint $table) {
            $table->dropColumn('lock_days');
        });

        Schema::table('copy_allocations', function (Blueprint $table) {
            $table->dropColumn('unlocks_at');
        });
    }
};
