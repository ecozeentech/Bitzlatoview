<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('copy_trader_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('display_name');
            $table->string('category')->default('crypto'); // crypto, forex, futures, stock, p2p
            $table->text('bio')->nullable();
            $table->string('strategy')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->string('status')->default('active');
            $table->string('risk_level')->default('medium');
            $table->decimal('return_30d', 10, 4)->default(0);
            $table->decimal('return_90d', 10, 4)->default(0);
            $table->decimal('max_drawdown', 10, 4)->default(0);
            $table->decimal('win_rate', 5, 2)->default(0);
            $table->unsignedInteger('followers')->default(0);
            $table->string('avatar')->nullable();
            $table->json('assets_traded')->nullable();
            $table->timestamps();
        });

        Schema::create('copy_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('copy_trader_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->decimal('allocation_amount', 28, 8);
            $table->decimal('copy_ratio', 8, 4)->default(1);
            $table->decimal('stop_loss', 10, 4)->nullable();
            $table->decimal('take_profit', 10, 4)->nullable();
            $table->decimal('max_position_size', 28, 8)->nullable();
            $table->string('status')->default('active'); // active, paused, stopped
            $table->decimal('pnl', 28, 8)->default(0);
            $table->boolean('is_simulated')->default(true);
            $table->timestamps();
        });

        Schema::create('copied_trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('copy_allocation_id')->constrained()->cascadeOnDelete();
            $table->string('symbol');
            $table->string('side');
            $table->decimal('price', 24, 8);
            $table->decimal('quantity', 28, 8);
            $table->decimal('pnl', 28, 8)->default(0);
            $table->boolean('is_simulated')->default(true);
            $table->timestamps();
        });

        Schema::create('ai_bots', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('strategy_type'); // conservative, balanced, aggressive, grid, dca, trend, arbitrage
            $table->text('description')->nullable();
            $table->string('risk_level')->default('medium');
            $table->decimal('risk_score', 5, 2)->default(5);
            $table->decimal('max_drawdown', 10, 4)->default(0);
            $table->decimal('simulated_return_30d', 10, 4)->default(0);
            $table->decimal('min_allocation', 28, 8)->default(100);
            $table->json('supported_assets')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_simulated')->default(true);
            $table->timestamps();
        });

        Schema::create('ai_bot_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ai_bot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 28, 8);
            $table->string('status')->default('active');
            $table->decimal('pnl', 28, 8)->default(0);
            $table->timestamp('lock_until')->nullable();
            $table->boolean('is_simulated')->default(true);
            $table->timestamps();
        });

        Schema::create('ai_bot_trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_bot_allocation_id')->constrained()->cascadeOnDelete();
            $table->string('symbol');
            $table->string('side');
            $table->decimal('price', 24, 8);
            $table->decimal('quantity', 28, 8);
            $table->decimal('pnl', 28, 8)->default(0);
            $table->boolean('is_simulated')->default(true);
            $table->timestamps();
        });

        Schema::create('mining_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->decimal('hashrate', 18, 4);
            $table->string('hashrate_unit')->default('TH/s');
            $table->unsignedInteger('term_days');
            $table->decimal('price', 28, 8);
            $table->foreignId('price_asset_id')->constrained('assets');
            $table->decimal('maintenance_fee_daily', 28, 8)->default(0);
            $table->decimal('estimated_daily_reward', 28, 8)->default(0);
            $table->text('risk_disclosure')->nullable();
            $table->boolean('is_published')->default(true);
            $table->boolean('is_simulated')->default(true);
            $table->timestamps();
        });

        Schema::create('mining_contracts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mining_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_account_id')->constrained()->cascadeOnDelete();
            $table->decimal('hashrate', 18, 4);
            $table->decimal('purchase_amount', 28, 8);
            $table->string('status')->default('active');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('reward_wallet')->default('INVESTMENT');
            $table->boolean('is_simulated')->default(true);
            $table->timestamps();
        });

        Schema::create('mining_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mining_contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 28, 8);
            $table->date('reward_date');
            $table->string('status')->default('credited');
            $table->boolean('is_simulated')->default(true);
            $table->timestamps();
        });

        Schema::create('investment_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->decimal('apy_estimate', 8, 4)->default(0);
            $table->unsignedInteger('lock_days')->default(0);
            $table->decimal('min_amount', 28, 8)->default(10);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_simulated')->default(true);
            $table->text('risk_disclosure')->nullable();
            $table->timestamps();
        });

        Schema::create('investment_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('investment_product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_account_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 28, 8);
            $table->string('status')->default('active');
            $table->timestamp('lock_until')->nullable();
            $table->decimal('accrued_reward', 28, 8)->default(0);
            $table->boolean('is_simulated')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_subscriptions');
        Schema::dropIfExists('investment_products');
        Schema::dropIfExists('mining_rewards');
        Schema::dropIfExists('mining_contracts');
        Schema::dropIfExists('mining_packages');
        Schema::dropIfExists('ai_bot_trades');
        Schema::dropIfExists('ai_bot_allocations');
        Schema::dropIfExists('ai_bots');
        Schema::dropIfExists('copied_trades');
        Schema::dropIfExists('copy_allocations');
        Schema::dropIfExists('copy_trader_profiles');
    }
};
