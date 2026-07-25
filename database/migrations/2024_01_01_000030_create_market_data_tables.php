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
            $table->string('type')->default('crypto'); // crypto, fiat, stock
            $table->unsignedTinyInteger('decimals')->default(8);
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('networks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('asset_networks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('network_id')->constrained()->cascadeOnDelete();
            $table->decimal('deposit_min', 36, 18)->default(0);
            $table->decimal('withdrawal_fee', 36, 18)->default(0);
            $table->unsignedInteger('confirmations_required')->default(1);
            $table->string('contract_address')->nullable();
            $table->timestamps();
            $table->unique(['asset_id', 'network_id']);
        });

        Schema::create('market_pairs', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->unique(); // BTC-USDT
            $table->foreignId('base_asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('quote_asset_id')->constrained('assets')->cascadeOnDelete();
            $table->decimal('min_qty', 36, 18)->default(0.0001);
            $table->unsignedTinyInteger('price_precision')->default(2);
            $table->unsignedTinyInteger('qty_precision')->default(6);
            $table->decimal('maker_fee_pct', 8, 4)->default(0.1);
            $table->decimal('taker_fee_pct', 8, 4)->default(0.1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_pair_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 36, 18)->default(0);
            $table->decimal('change_24h_pct', 8, 4)->default(0);
            $table->decimal('high_24h', 36, 18)->default(0);
            $table->decimal('low_24h', 36, 18)->default(0);
            $table->decimal('volume_24h', 36, 18)->default(0);
            $table->timestamps();
            $table->unique('market_pair_id');
        });

        Schema::create('candles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_pair_id')->constrained()->cascadeOnDelete();
            $table->string('interval')->default('1h');
            $table->timestamp('open_time');
            $table->decimal('open', 36, 18);
            $table->decimal('high', 36, 18);
            $table->decimal('low', 36, 18);
            $table->decimal('close', 36, 18);
            $table->decimal('volume', 36, 18)->default(0);
            $table->index(['market_pair_id', 'interval', 'open_time']);
        });

        Schema::create('watchlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('market_pair_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'market_pair_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watchlist_items');
        Schema::dropIfExists('candles');
        Schema::dropIfExists('quotes');
        Schema::dropIfExists('market_pairs');
        Schema::dropIfExists('asset_networks');
        Schema::dropIfExists('networks');
        Schema::dropIfExists('assets');
    }
};
