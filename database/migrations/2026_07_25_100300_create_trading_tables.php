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
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('market_pair_id')->constrained()->cascadeOnDelete();
            $table->string('side'); // buy, sell
            $table->string('type'); // market, limit, stop_limit, stop_market, oco
            $table->string('status')->default('new');
            $table->decimal('price', 24, 8)->nullable();
            $table->decimal('stop_price', 24, 8)->nullable();
            $table->decimal('quantity', 28, 8);
            $table->decimal('filled_quantity', 28, 8)->default(0);
            $table->decimal('avg_fill_price', 24, 8)->nullable();
            $table->decimal('fee', 28, 8)->default(0);
            $table->boolean('is_simulated')->default(true);
            $table->timestamp('filled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('market_pair_id')->constrained()->cascadeOnDelete();
            $table->string('side');
            $table->decimal('price', 24, 8);
            $table->decimal('quantity', 28, 8);
            $table->decimal('fee', 28, 8)->default(0);
            $table->string('fee_asset')->nullable();
            $table->boolean('is_simulated')->default(true);
            $table->timestamps();
        });

        Schema::create('swap_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_wallet_account_id')->constrained('wallet_accounts');
            $table->foreignId('to_wallet_account_id')->constrained('wallet_accounts');
            $table->foreignId('from_asset_id')->constrained('assets');
            $table->foreignId('to_asset_id')->constrained('assets');
            $table->decimal('from_amount', 28, 8);
            $table->decimal('to_amount', 28, 8);
            $table->decimal('rate', 24, 8);
            $table->decimal('fee', 28, 8)->default(0);
            $table->decimal('slippage', 8, 4)->default(0.5);
            $table->decimal('price_impact', 8, 4)->default(0);
            $table->string('status')->default('completed');
            $table->boolean('is_simulated')->default(true);
            $table->timestamps();
        });

        Schema::create('stock_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_instrument_id')->constrained()->cascadeOnDelete();
            $table->string('side');
            $table->string('type')->default('market');
            $table->decimal('quantity', 18, 6);
            $table->decimal('price', 18, 4)->nullable();
            $table->string('status')->default('filled');
            $table->boolean('is_simulated')->default(true);
            $table->timestamps();
        });

        Schema::create('stock_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_instrument_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 18, 6)->default(0);
            $table->decimal('avg_cost', 18, 4)->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'stock_instrument_id']);
        });

        Schema::create('forex_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('forex_pair_id')->constrained()->cascadeOnDelete();
            $table->string('side');
            $table->decimal('lots', 12, 4)->default(0.01);
            $table->decimal('price', 18, 6)->nullable();
            $table->unsignedInteger('leverage')->default(50);
            $table->string('status')->default('filled');
            $table->boolean('is_simulated')->default(true);
            $table->timestamps();
        });

        Schema::create('forex_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('forex_pair_id')->constrained()->cascadeOnDelete();
            $table->string('side');
            $table->decimal('lots', 12, 4);
            $table->decimal('entry_price', 18, 6);
            $table->decimal('unrealized_pnl', 18, 6)->default(0);
            $table->string('status')->default('open');
            $table->boolean('is_simulated')->default(true);
            $table->timestamps();
        });

        Schema::create('futures_positions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('futures_market_id')->constrained()->cascadeOnDelete();
            $table->string('side'); // long, short
            $table->string('margin_mode')->default('isolated');
            $table->unsignedInteger('leverage')->default(5);
            $table->decimal('size', 28, 8);
            $table->decimal('entry_price', 24, 8);
            $table->decimal('mark_price', 24, 8)->nullable();
            $table->decimal('liquidation_price', 24, 8)->nullable();
            $table->decimal('unrealized_pnl', 28, 8)->default(0);
            $table->decimal('margin', 28, 8)->default(0);
            $table->string('status')->default('open');
            $table->boolean('is_simulated')->default(true);
            $table->timestamps();
        });

        Schema::create('fee_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('module'); // spot, swap, futures, p2p, withdrawal
            $table->decimal('maker_fee_bps', 10, 4)->default(10);
            $table->decimal('taker_fee_bps', 10, 4)->default(10);
            $table->decimal('flat_fee', 28, 8)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_schedules');
        Schema::dropIfExists('futures_positions');
        Schema::dropIfExists('forex_positions');
        Schema::dropIfExists('forex_orders');
        Schema::dropIfExists('stock_positions');
        Schema::dropIfExists('stock_orders');
        Schema::dropIfExists('swap_transactions');
        Schema::dropIfExists('trades');
        Schema::dropIfExists('orders');
    }
};
