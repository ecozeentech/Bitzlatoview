<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('copy_allocations', function (Blueprint $table) {
            $table->decimal('minimum_amount', 20, 2)->default(100)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('copy_allocations', function (Blueprint $table) {
            $table->dropColumn('minimum_amount');
        });
    }
};
