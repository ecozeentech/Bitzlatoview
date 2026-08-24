<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trader_profiles', function (Blueprint $table) {
            $table->decimal('min_copy_amount', 20, 2)->default(50)->after('followers_count');
        });
    }

    public function down(): void
    {
        Schema::table('trader_profiles', function (Blueprint $table) {
            $table->dropColumn('min_copy_amount');
        });
    }
};
