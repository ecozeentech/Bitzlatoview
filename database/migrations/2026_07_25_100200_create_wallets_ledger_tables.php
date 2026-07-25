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
            $table->string('type'); // PRIMARY, TRADING, INVESTMENT
            $table->string('status')->default('active');
            $table->timestamps();
            $table->unique(['user_id', 'type']);
        });

        Schema::create('balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->decimal('available', 28, 8)->default(0);
            $table->decimal('locked', 28, 8)->default(0);
            $table->timestamps();
            $table->unique(['wallet_account_id', 'asset_id']);
        });

        Schema::create('ledger_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('idempotency_key')->unique();
            $table->string('type'); // deposit, withdrawal, transfer, trade, swap, p2p, mining, bot, adjustment, card_funding
            $table->string('status')->default('completed');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ledger_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->string('entry_type'); // debit, credit
            $table->string('balance_bucket')->default('available'); // available, locked
            $table->decimal('amount', 28, 8);
            $table->decimal('balance_after', 28, 8)->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('funding_notes', function (Blueprint $table) {
            $table->id();
            $table->string('notable_type');
            $table->unsignedBigInteger('notable_id');
            $table->text('user_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->text('compliance_note')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('evidence_url')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['notable_type', 'notable_id']);
        });

        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('network_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 28, 8);
            $table->string('status')->default('pending');
            $table->string('address')->nullable();
            $table->string('tx_hash')->nullable();
            $table->unsignedInteger('confirmations')->default(0);
            $table->boolean('is_simulated')->default(true);
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('network_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 28, 8);
            $table->decimal('fee', 28, 8)->default(0);
            $table->string('destination_address');
            $table->string('status')->default('pending_review');
            $table->boolean('is_simulated')->default(true);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_wallet_account_id')->constrained('wallet_accounts');
            $table->foreignId('to_wallet_account_id')->constrained('wallet_accounts');
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 28, 8);
            $table->string('status')->default('completed');
            $table->timestamps();
        });

        Schema::create('withdrawal_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('network_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label');
            $table->string('address');
            $table->boolean('is_whitelisted')->default(false);
            $table->timestamp('cooldown_until')->nullable();
            $table->timestamps();
        });

        Schema::create('manual_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 28, 8);
            $table->string('direction'); // credit, debit
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->string('reason');
            $table->string('evidence_url')->nullable();
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_adjustments');
        Schema::dropIfExists('withdrawal_addresses');
        Schema::dropIfExists('transfers');
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('deposits');
        Schema::dropIfExists('funding_notes');
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('ledger_transactions');
        Schema::dropIfExists('balances');
        Schema::dropIfExists('wallet_accounts');
    }
};
