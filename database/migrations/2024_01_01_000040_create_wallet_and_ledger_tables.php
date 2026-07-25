<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // primary, trading, investment
            $table->timestamps();
            $table->unique(['user_id', 'type']);
        });

        Schema::create('balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->decimal('available', 36, 18)->default(0);
            $table->decimal('locked', 36, 18)->default(0);
            $table->timestamps();
            $table->unique(['wallet_account_id', 'asset_id']);
        });

        Schema::create('ledger_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('idempotency_key')->unique();
            $table->string('reference_type'); // deposit, withdrawal, transfer, order, trade, swap, p2p_order, admin_adjustment, mining_reward, bot_pnl, investment_reward, card_transaction
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ledger_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->string('direction'); // debit, credit
            $table->decimal('amount', 36, 18);
            $table->decimal('balance_after', 36, 18)->nullable();
            $table->timestamps();
        });

        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('network_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 36, 18);
            $table->string('address')->nullable();
            $table->string('tx_hash')->nullable();
            $table->string('status')->default('pending'); // pending, confirming, credited, rejected, failed
            $table->text('user_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->text('compliance_note')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('evidence_url')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('credited_at')->nullable();
            $table->timestamps();
        });

        Schema::create('withdrawal_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('network_id')->nullable()->constrained()->nullOnDelete();
            $table->string('address');
            $table->string('label')->nullable();
            $table->boolean('is_whitelisted')->default(false);
            $table->timestamp('cooldown_until')->nullable();
            $table->timestamps();
        });

        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('network_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('withdrawal_address_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 36, 18);
            $table->decimal('fee', 36, 18)->default(0);
            $table->string('address');
            $table->string('status')->default('draft');
            // draft, pending_review, approved, processing, completed, rejected, failed
            $table->text('user_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->text('compliance_note')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('evidence_url')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_wallet_account_id')->constrained('wallet_accounts')->cascadeOnDelete();
            $table->foreignId('to_wallet_account_id')->constrained('wallet_accounts')->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 36, 18);
            $table->text('user_note')->nullable();
            $table->string('status')->default('completed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfers');
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('withdrawal_addresses');
        Schema::dropIfExists('deposits');
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('ledger_transactions');
        Schema::dropIfExists('balances');
        Schema::dropIfExists('wallet_accounts');
    }
};
