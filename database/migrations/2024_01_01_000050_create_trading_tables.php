<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('market_pair_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_account_id')->constrained()->cascadeOnDelete();
            $table->string('side'); // buy, sell
            $table->string('type'); // market, limit, stop_limit, stop_market, oco
            $table->decimal('price', 36, 18)->nullable();
            $table->decimal('stop_price', 36, 18)->nullable();
            $table->decimal('quantity', 36, 18);
            $table->decimal('filled_quantity', 36, 18)->default(0);
            $table->string('status')->default('new'); // new, partially_filled, filled, cancelled, rejected, expired
            $table->timestamps();
        });

        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 36, 18);
            $table->decimal('quantity', 36, 18);
            $table->decimal('fee', 36, 18)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trades');
        Schema::dropIfExists('orders');
    }
};
