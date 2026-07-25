<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mt5_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('broker_name');
            $table->string('mt5_login');
            $table->string('server_name');
            $table->string('account_type')->default('demo');
            $table->unsignedInteger('leverage')->default(100);
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('connected');
            // PROVIDER: Encrypt credentials via broker OAuth/API — never store MT5 passwords in plain text.
            $table->text('encrypted_credentials')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->boolean('is_simulated')->default(true);
            $table->timestamps();
        });

        Schema::create('mt5_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mt5_account_id')->constrained()->cascadeOnDelete();
            $table->string('symbol');
            $table->string('side');
            $table->decimal('volume', 18, 4);
            $table->decimal('open_price', 18, 6);
            $table->decimal('current_price', 18, 6)->nullable();
            $table->decimal('profit', 18, 6)->default(0);
            $table->string('status')->default('open');
            $table->boolean('is_simulated')->default(true);
            $table->timestamps();
        });

        Schema::create('mt5_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mt5_account_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->text('message')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('cardholders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('legal_name');
            $table->string('status')->default('pending');
            // PROVIDER: Connect Stripe Issuing / Marqeta / Lithic for real cardholder creation.
            $table->string('provider_ref')->nullable();
            $table->timestamps();
        });

        Schema::create('virtual_cards', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cardholder_id')->constrained()->cascadeOnDelete();
            $table->string('nickname')->nullable();
            $table->string('last_four', 4);
            $table->string('brand')->default('Visa');
            $table->string('currency', 3)->default('USD');
            $table->decimal('spending_limit', 18, 2)->default(1000);
            $table->decimal('spent_amount', 18, 2)->default(0);
            $table->string('status')->default('pending');
            $table->string('masked_pan')->default('**** **** **** ****');
            // PROVIDER: Never persist full PAN; use issuer vault/reveal APIs.
            $table->string('provider_ref')->nullable();
            $table->boolean('is_simulated')->default(true);
            $table->timestamps();
        });

        Schema::create('card_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('virtual_card_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 18, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('merchant_name')->nullable();
            $table->string('status')->default('settled');
            $table->string('type')->default('purchase');
            $table->boolean('is_simulated')->default(true);
            $table->timestamps();
        });

        Schema::create('connected_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('address');
            $table->string('chain')->default('ethereum');
            $table->string('wallet_type')->nullable(); // metamask, trust, coinbase, rainbow, ledger, walletconnect
            $table->string('label')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamp('last_connected_at')->nullable();
            // PROVIDER: WalletConnect project ID / wagmi / viem for real sessions.
            $table->timestamps();
            $table->unique(['user_id', 'address', 'chain']);
        });

        Schema::create('nft_collections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->decimal('floor_price', 24, 8)->default(0);
            $table->decimal('volume_24h', 24, 8)->default(0);
            $table->unsignedInteger('owners')->default(0);
            $table->unsignedInteger('items_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('nft_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nft_collection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('token_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->decimal('last_price', 24, 8)->nullable();
            $table->string('rarity')->nullable();
            $table->json('attributes')->nullable();
            $table->timestamps();
        });

        Schema::create('nft_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nft_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('price', 24, 8);
            $table->string('currency')->default('ETH');
            $table->string('status')->default('active');
            $table->boolean('is_simulated')->default(true);
            $table->timestamps();
        });

        Schema::create('billing_packages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('analyst_name')->nullable();
            $table->string('analyst_credential')->nullable();
            $table->boolean('credential_verified')->default(false);
            $table->decimal('price', 18, 2);
            $table->string('billing_cycle')->default('monthly');
            $table->json('features')->nullable();
            $table->unsignedInteger('report_access')->default(0);
            $table->unsignedInteger('consultation_minutes')->default(0);
            $table->text('risk_disclosure')->nullable();
            $table->string('invoice_label')->default('Market Analyst Package');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('billing_package_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('line_item');
            $table->decimal('amount', 18, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('paid');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('tax_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 28, 8);
            $table->decimal('cost_basis', 28, 8);
            $table->timestamp('acquired_at');
            $table->decimal('remaining_quantity', 28, 8);
            $table->timestamps();
        });

        Schema::create('tax_transaction_classifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference_type');
            $table->unsignedBigInteger('reference_id');
            $table->string('classification'); // trade, income, fee, transfer, mining, bot, p2p, nft
            $table->string('cost_basis_method')->default('FIFO');
            $table->decimal('realized_gain', 28, 8)->default(0);
            $table->unsignedSmallInteger('tax_year');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('tax_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('tax_year');
            $table->string('country', 2)->default('US');
            $table->string('cost_basis_method')->default('FIFO');
            $table->decimal('realized_gains', 28, 8)->default(0);
            $table->decimal('realized_losses', 28, 8)->default(0);
            $table->decimal('income_total', 28, 8)->default(0);
            $table->decimal('fees_paid', 28, 8)->default(0);
            $table->string('status')->default('draft');
            $table->string('file_path')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'tax_year', 'country']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_reports');
        Schema::dropIfExists('tax_transaction_classifications');
        Schema::dropIfExists('tax_lots');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('billing_packages');
        Schema::dropIfExists('nft_listings');
        Schema::dropIfExists('nft_items');
        Schema::dropIfExists('nft_collections');
        Schema::dropIfExists('connected_wallets');
        Schema::dropIfExists('card_transactions');
        Schema::dropIfExists('virtual_cards');
        Schema::dropIfExists('cardholders');
        Schema::dropIfExists('mt5_sync_logs');
        Schema::dropIfExists('mt5_positions');
        Schema::dropIfExists('mt5_accounts');
    }
};
