<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swap_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('to_asset_id')->constrained('assets')->cascadeOnDelete();
            $table->decimal('from_amount', 36, 18);
            $table->decimal('to_amount', 36, 18);
            $table->decimal('rate', 36, 18);
            $table->decimal('fee', 36, 18)->default(0);
            $table->decimal('slippage_pct', 8, 4)->default(0.5);
            $table->string('status')->default('completed'); // quoted, completed, failed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('swap_transactions');
    }
};
