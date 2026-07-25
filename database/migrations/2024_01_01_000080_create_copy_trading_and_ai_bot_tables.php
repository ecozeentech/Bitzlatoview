<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trader_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('display_name');
            $table->string('avatar')->nullable();
            $table->string('category'); // crypto, forex, futures, stock, p2p
            $table->text('bio')->nullable();
            $table->text('strategy')->nullable();
            $table->unsignedTinyInteger('risk_score')->default(50); // 0-100
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->decimal('return_30d_pct', 8, 2)->default(0);
            $table->decimal('return_90d_pct', 8, 2)->default(0);
            $table->decimal('max_drawdown_pct', 8, 2)->default(0);
            $table->decimal('win_rate_pct', 5, 2)->default(0);
            $table->unsignedInteger('followers_count')->default(0);
            $table->string('status')->default('active'); // active, suspended, pending_approval
            $table->timestamps();
        });

        Schema::create('trader_performance_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trader_profile_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('return_pct', 8, 2)->default(0);
            $table->decimal('drawdown_pct', 8, 2)->default(0);
            $table->decimal('aum', 20, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('copy_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trader_profile_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 20, 2);
            $table->decimal('stop_loss_pct', 8, 2)->nullable();
            $table->decimal('take_profit_pct', 8, 2)->nullable();
            $table->decimal('max_position_size', 20, 2)->nullable();
            $table->decimal('copy_ratio', 5, 2)->default(1);
            $table->string('status')->default('active'); // active, paused, stopped
            $table->decimal('pnl', 20, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('copied_trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('copy_allocation_id')->constrained()->cascadeOnDelete();
            $table->string('asset_symbol');
            $table->string('side');
            $table->decimal('entry_price', 36, 18);
            $table->decimal('exit_price', 36, 18)->nullable();
            $table->decimal('pnl', 20, 2)->default(0);
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_bots', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('strategy_type'); // conservative, balanced, aggressive, grid, dca, trend, arbitrage
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('risk_score')->default(50);
            $table->decimal('min_allocation', 20, 2)->default(50);
            $table->json('supported_assets')->nullable();
            $table->decimal('historical_return_pct', 8, 2)->default(0);
            $table->decimal('max_drawdown_pct', 8, 2)->default(0);
            $table->unsignedInteger('lock_days')->default(0);
            $table->string('status')->default('active'); // active, paused, retired
            $table->timestamps();
        });

        Schema::create('ai_bot_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ai_bot_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 20, 2);
            $table->decimal('pnl', 20, 2)->default(0);
            $table->string('status')->default('active'); // active, paused, stopped
            $table->timestamp('started_at')->nullable();
            $table->timestamp('stopped_at')->nullable();
            $table->timestamp('unlocks_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_bot_trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_bot_allocation_id')->constrained()->cascadeOnDelete();
            $table->string('asset_symbol');
            $table->string('side');
            $table->decimal('amount', 20, 2);
            $table->decimal('pnl', 20, 2)->default(0);
            $table->timestamp('executed_at');
            $table->timestamps();
        });

        Schema::create('ai_bot_performance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_bot_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('return_pct', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_bot_performance');
        Schema::dropIfExists('ai_bot_trades');
        Schema::dropIfExists('ai_bot_allocations');
        Schema::dropIfExists('ai_bots');
        Schema::dropIfExists('copied_trades');
        Schema::dropIfExists('copy_allocations');
        Schema::dropIfExists('trader_performance_snapshots');
        Schema::dropIfExists('trader_profiles');
    }
};
