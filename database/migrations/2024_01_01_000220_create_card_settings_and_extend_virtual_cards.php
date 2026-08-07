<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('max_spending_limit', 20, 2)->default(10000);
            $table->json('allowed_currencies')->default(json_encode(['USD', 'EUR', 'GBP']));
            $table->decimal('issuance_fee', 20, 2)->default(0);
            $table->decimal('funding_fee_pct', 8, 4)->default(0);
            $table->decimal('monthly_fee', 20, 2)->default(0);
            $table->timestamps();
        });

        Schema::table('virtual_cards', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('status');
            $table->timestamp('approved_at')->nullable()->after('rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('virtual_cards', function (Blueprint $table) {
            $table->dropColumn(['rejection_reason', 'approved_at']);
        });
        Schema::dropIfExists('card_settings');
    }
};
