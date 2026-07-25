<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mining_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->decimal('hashrate_th', 12, 2); // TH/s
            $table->unsignedInteger('term_days');
            $table->decimal('maintenance_fee_pct', 8, 4)->default(2);
            $table->decimal('price', 20, 2);
            $table->decimal('estimated_daily_reward_pct', 8, 4)->default(0.1);
            $table->text('risk_disclosure')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('mining_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mining_package_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount_invested', 20, 2);
            $table->string('reward_destination')->default('investment'); // primary, investment
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('active'); // active, completed, cancelled
            $table->timestamps();
        });

        Schema::create('mining_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mining_contract_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 36, 18);
            $table->timestamp('credited_at');
            $table->timestamps();
        });

        Schema::create('investment_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->decimal('apy_pct', 8, 2);
            $table->unsignedInteger('lock_days')->default(0);
            $table->decimal('min_amount', 20, 2)->default(10);
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active, paused
            $table->timestamps();
        });

        Schema::create('investment_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('investment_product_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 20, 2);
            $table->date('start_date');
            $table->date('unlock_date')->nullable();
            $table->string('status')->default('active'); // active, redeemed, matured
            $table->timestamps();
        });

        Schema::create('investment_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_subscription_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 36, 18);
            $table->timestamp('credited_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_rewards');
        Schema::dropIfExists('investment_subscriptions');
        Schema::dropIfExists('investment_products');
        Schema::dropIfExists('mining_rewards');
        Schema::dropIfExists('mining_contracts');
        Schema::dropIfExists('mining_packages');
    }
};
