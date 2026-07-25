<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p2p_merchant_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('display_name');
            $table->boolean('is_verified')->default(false);
            $table->unsignedInteger('completed_orders')->default(0);
            $table->decimal('completion_rate', 5, 2)->default(100);
            $table->decimal('positive_feedback_rate', 5, 2)->default(100);
            $table->unsignedInteger('avg_release_minutes')->default(15);
            $table->string('status')->default('active'); // active, suspended
            $table->timestamps();
            $table->unique('user_id');
        });

        Schema::create('p2p_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // bank_transfer, mobile_money, paypal, revolut, etc.
            $table->string('account_name');
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();
        });

        Schema::create('p2p_ads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('side'); // buy, sell
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->string('fiat_currency', 8);
            $table->string('price_type')->default('fixed'); // fixed, floating
            $table->decimal('price', 20, 4);
            $table->decimal('min_limit', 20, 4);
            $table->decimal('max_limit', 20, 4);
            $table->decimal('available_amount', 36, 18);
            $table->json('payment_method_ids')->nullable();
            $table->text('terms')->nullable();
            $table->text('auto_reply')->nullable();
            $table->string('region')->nullable();
            $table->string('status')->default('active'); // active, paused, closed
            $table->timestamps();
        });

        Schema::create('p2p_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p2p_ad_id')->constrained()->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->string('fiat_currency', 8);
            $table->decimal('crypto_amount', 36, 18);
            $table->decimal('fiat_amount', 20, 4);
            $table->decimal('price', 20, 4);
            $table->string('payment_method')->nullable();
            $table->string('status')->default('created');
            // created, escrow_locked, awaiting_payment, paid, released, cancelled, appealed, refunded, completed
            $table->text('user_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('p2p_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p2p_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });

        Schema::create('p2p_appeals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p2p_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raised_by')->constrained('users')->cascadeOnDelete();
            $table->text('reason');
            $table->string('evidence_url')->nullable();
            $table->string('status')->default('open'); // open, reviewing, resolved
            $table->text('resolution')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('p2p_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p2p_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->string('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p2p_feedback');
        Schema::dropIfExists('p2p_appeals');
        Schema::dropIfExists('p2p_messages');
        Schema::dropIfExists('p2p_orders');
        Schema::dropIfExists('p2p_ads');
        Schema::dropIfExists('p2p_payment_methods');
        Schema::dropIfExists('p2p_merchant_profiles');
    }
};
