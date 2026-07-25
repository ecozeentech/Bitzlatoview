<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_instruments', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->unique();
            $table->string('name');
            $table->string('exchange')->default('NASDAQ');
            $table->decimal('last_price', 20, 4)->default(0);
            $table->decimal('change_pct', 8, 4)->default(0);
            $table->timestamps();
        });

        Schema::create('stock_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_instrument_id')->constrained()->cascadeOnDelete();
            $table->string('side');
            $table->decimal('quantity', 20, 6);
            $table->decimal('price', 20, 4);
            $table->string('status')->default('filled'); // paper trading, auto-filled
            $table->timestamps();
        });

        Schema::create('stock_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_instrument_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 20, 6)->default(0);
            $table->decimal('avg_price', 20, 4)->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'stock_instrument_id']);
        });

        Schema::create('forex_pairs', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->unique(); // EUR/USD
            $table->string('base_currency', 8);
            $table->string('quote_currency', 8);
            $table->decimal('bid', 12, 5)->default(0);
            $table->decimal('ask', 12, 5)->default(0);
            $table->decimal('spread_pips', 8, 2)->default(1);
            $table->timestamps();
        });

        Schema::create('forex_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('forex_pair_id')->constrained()->cascadeOnDelete();
            $table->string('side');
            $table->decimal('lot_size', 10, 2);
            $table->unsignedInteger('leverage')->default(50);
            $table->decimal('entry_price', 12, 5);
            $table->string('status')->default('filled');
            $table->timestamps();
        });

        Schema::create('forex_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('forex_pair_id')->constrained()->cascadeOnDelete();
            $table->string('side');
            $table->decimal('lot_size', 10, 2);
            $table->unsignedInteger('leverage')->default(50);
            $table->decimal('entry_price', 12, 5);
            $table->decimal('current_price', 12, 5)->default(0);
            $table->decimal('pnl', 20, 2)->default(0);
            $table->string('status')->default('open'); // open, closed
            $table->timestamps();
        });

        Schema::create('futures_markets', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->unique(); // BTCUSDT-PERP
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('max_leverage')->default(20);
            $table->decimal('maintenance_margin_pct', 8, 4)->default(0.5);
            $table->decimal('mark_price', 36, 18)->default(0);
            $table->decimal('index_price', 36, 18)->default(0);
            $table->decimal('funding_rate_pct', 8, 4)->default(0.01);
            $table->timestamps();
        });

        Schema::create('futures_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('futures_market_id')->constrained()->cascadeOnDelete();
            $table->string('side'); // long, short
            $table->unsignedInteger('leverage')->default(5);
            $table->string('margin_mode')->default('isolated'); // cross, isolated
            $table->string('position_mode')->default('one_way'); // one_way, hedge
            $table->decimal('entry_price', 36, 18);
            $table->decimal('quantity', 36, 18);
            $table->decimal('margin', 20, 2);
            $table->decimal('liquidation_price', 36, 18)->nullable();
            $table->decimal('pnl', 20, 2)->default(0);
            $table->string('status')->default('open'); // open, closed, liquidated
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('futures_funding_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('futures_position_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 20, 2);
            $table->timestamp('paid_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('futures_funding_payments');
        Schema::dropIfExists('futures_positions');
        Schema::dropIfExists('futures_markets');
        Schema::dropIfExists('forex_positions');
        Schema::dropIfExists('forex_orders');
        Schema::dropIfExists('forex_pairs');
        Schema::dropIfExists('stock_positions');
        Schema::dropIfExists('stock_orders');
        Schema::dropIfExists('stock_instruments');
    }
};
