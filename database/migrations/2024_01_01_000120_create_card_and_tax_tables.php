<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('virtual_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nickname')->nullable();
            $table->string('cardholder_name');
            $table->string('masked_number'); // **** **** **** 1234
            $table->string('last_four', 4);
            $table->string('expiry_month', 2);
            $table->string('expiry_year', 4);
            $table->string('currency', 8)->default('USD');
            $table->decimal('spending_limit', 20, 2)->default(1000);
            $table->foreignId('funding_wallet_account_id')->constrained('wallet_accounts')->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, active, frozen, cancelled, expired
            $table->timestamps();
        });

        Schema::create('card_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('virtual_card_id')->constrained()->cascadeOnDelete();
            $table->string('merchant');
            $table->decimal('amount', 20, 2);
            $table->string('status')->default('authorized'); // authorized, settled, declined, reversed, refunded
            $table->timestamp('occurred_at');
            $table->timestamps();
        });

        Schema::create('tax_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 36, 18);
            $table->decimal('cost_basis', 20, 4);
            $table->timestamp('acquired_at');
            $table->timestamp('disposed_at')->nullable();
            $table->decimal('proceeds', 20, 4)->nullable();
            $table->timestamps();
        });

        Schema::create('tax_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('country')->nullable();
            $table->string('cost_basis_method')->default('fifo'); // fifo, lifo, hifo, average
            $table->decimal('realized_gain', 20, 4)->default(0);
            $table->decimal('unrealized_gain', 20, 4)->default(0);
            $table->decimal('income_total', 20, 4)->default(0);
            $table->decimal('fees_paid', 20, 4)->default(0);
            $table->string('file_path')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('tax_transaction_classifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference_type');
            $table->unsignedBigInteger('reference_id');
            $table->string('classification'); // trade, income, mining, staking, referral, airdrop, gift, transfer
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_transaction_classifications');
        Schema::dropIfExists('tax_reports');
        Schema::dropIfExists('tax_lots');
        Schema::dropIfExists('card_transactions');
        Schema::dropIfExists('virtual_cards');
    }
};
