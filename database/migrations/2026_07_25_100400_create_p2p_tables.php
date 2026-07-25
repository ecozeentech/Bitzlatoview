<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p2p_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('p2p_merchant_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_verified')->default(false);
            $table->unsignedInteger('completed_trades')->default(0);
            $table->decimal('completion_rate', 5, 2)->default(100);
            $table->decimal('positive_feedback_rate', 5, 2)->default(100);
            $table->unsignedInteger('avg_release_minutes')->default(15);
            $table->boolean('is_online')->default(true);
            $table->text('terms')->nullable();
            $table->text('auto_reply')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('p2p_ads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('merchant_profile_id')->nullable()->constrained('p2p_merchant_profiles')->nullOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->string('side'); // buy, sell
            $table->string('fiat_currency', 3)->default('USD');
            $table->string('price_type')->default('fixed'); // fixed, floating
            $table->decimal('price', 24, 8);
            $table->decimal('available_amount', 28, 8);
            $table->decimal('min_limit', 24, 2);
            $table->decimal('max_limit', 24, 2);
            $table->json('payment_method_ids')->nullable();
            $table->text('terms')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('p2p_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('ad_id')->constrained('p2p_ads')->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->string('fiat_currency', 3);
            $table->decimal('crypto_amount', 28, 8);
            $table->decimal('fiat_amount', 24, 2);
            $table->decimal('price', 24, 8);
            $table->string('payment_method')->nullable();
            $table->string('status')->default('created');
            $table->timestamp('payment_deadline')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->boolean('is_simulated')->default(true);
            $table->timestamps();
        });

        Schema::create('p2p_escrows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p2p_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 28, 8);
            $table->string('status')->default('locked');
            $table->timestamps();
        });

        Schema::create('p2p_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p2p_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });

        Schema::create('p2p_appeals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p2p_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('opened_by')->constrained('users')->cascadeOnDelete();
            $table->text('reason');
            $table->string('evidence_url')->nullable();
            $table->string('status')->default('open');
            $table->text('admin_resolution')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('p2p_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p2p_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_positive')->default(true);
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p2p_feedback');
        Schema::dropIfExists('p2p_appeals');
        Schema::dropIfExists('p2p_messages');
        Schema::dropIfExists('p2p_escrows');
        Schema::dropIfExists('p2p_orders');
        Schema::dropIfExists('p2p_ads');
        Schema::dropIfExists('p2p_merchant_profiles');
        Schema::dropIfExists('p2p_payment_methods');
    }
};
