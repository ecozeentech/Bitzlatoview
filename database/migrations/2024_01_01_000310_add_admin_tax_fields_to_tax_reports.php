<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_reports', function (Blueprint $table) {
            $table->decimal('tax_rate_pct', 8, 4)->nullable()->after('fees_paid');
            $table->decimal('estimated_tax_owed', 20, 4)->nullable()->after('tax_rate_pct');
            $table->text('admin_notes')->nullable()->after('estimated_tax_owed');
        });
    }

    public function down(): void
    {
        Schema::table('tax_reports', function (Blueprint $table) {
            $table->dropColumn(['tax_rate_pct', 'estimated_tax_owed', 'admin_notes']);
        });
    }
};
