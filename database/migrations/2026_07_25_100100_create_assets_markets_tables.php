<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->unique();
            $table->string('name');
            $table->string('type')->default('crypto'); // crypto, fiat, stock, forex, nft
            $table->unsignedTinyInteger('decimals')->default(8);
            $table->boolean('is_active')->default(true);
            $table->string('icon')->nullable();
            $table->decimal('mock_price_usd', 24, 8)->default(0);
            $table->decimal('change_24h', 10, 4)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('networks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('asset_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('confirmations')->default(3);
            $table->decimal('min_deposit', 24, 8)->default(0);
            $table->decimal('withdrawal_fee', 24, 8)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('market_pairs', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->unique(); // BTC-USDT
            $table->foreignId('base_asset_id')->constrained('assets');
            $table->foreignId('quote_asset_id')->constrained('assets');
            $table->string('market_type')->default('spot'); // spot, futures
            $table->decimal('last_price', 24, 8)->default(0);
            $table->decimal('change_24h', 10, 4)->default(0);
            $table->decimal('high_24h', 24, 8)->default(0);
            $table->decimal('low_24h', 24, 8)->default(0);
            $table->decimal('volume_24h', 24, 8)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_pair_id')->constrained()->cascadeOnDelete();
            $table->decimal('bid', 24, 8);
            $table->decimal('ask', 24, 8);
            $table->decimal('last', 24, 8);
            $table->timestamp('quoted_at');
            $table->timestamps();
        });

        Schema::create('candles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_pair_id')->constrained()->cascadeOnDelete();
            $table->string('interval')->default('1h');
            $table->timestamp('open_time');
            $table->decimal('open', 24, 8);
            $table->decimal('high', 24, 8);
            $table->decimal('low', 24, 8);
            $table->decimal('close', 24, 8);
            $table->decimal('volume', 24, 8)->default(0);
            $table->timestamps();
            $table->unique(['market_pair_id', 'interval', 'open_time']);
        });

        Schema::create('watchlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->nullableMorphs('watchable');
            $table->string('symbol')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_instruments', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->unique();
            $table->string('name');
            $table->string('exchange')->default('NASDAQ');
            $table->decimal('last_price', 24, 4)->default(0);
            $table->decimal('change_24h', 10, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('paper_only')->default(true);
            $table->timestamps();
        });

        Schema::create('forex_pairs', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->unique();
            $table->string('base_currency', 3);
            $table->string('quote_currency', 3);
            $table->decimal('bid', 18, 6)->default(0);
            $table->decimal('ask', 18, 6)->default(0);
            $table->decimal('spread', 10, 6)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('paper_only')->default(true);
            $table->timestamps();
        });

        Schema::create('futures_markets', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->unique();
            $table->foreignId('base_asset_id')->constrained('assets');
            $table->string('contract_type')->default('perpetual');
            $table->decimal('mark_price', 24, 8)->default(0);
            $table->decimal('index_price', 24, 8)->default(0);
            $table->decimal('funding_rate', 16, 8)->default(0);
            $table->unsignedInteger('max_leverage')->default(20);
            $table->boolean('is_active')->default(true);
            $table->boolean('paper_only')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('futures_markets');
        Schema::dropIfExists('forex_pairs');
        Schema::dropIfExists('stock_instruments');
        Schema::dropIfExists('watchlist_items');
        Schema::dropIfExists('candles');
        Schema::dropIfExists('quotes');
        Schema::dropIfExists('market_pairs');
        Schema::dropIfExists('networks');
        Schema::dropIfExists('assets');
    }
};
