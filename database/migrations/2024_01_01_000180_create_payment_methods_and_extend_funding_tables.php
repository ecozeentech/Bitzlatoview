<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "USDT (TRC20)", "Chase Bank Wire", "CashApp"
            $table->string('type'); // crypto, bank_transfer, cashapp, venmo, paypal, other
            $table->string('currency', 16)->default('USDT'); // asset symbol or fiat code this method is denominated in
            $table->string('network')->nullable(); // for crypto: TRC20, ERC20, BEP20, etc.
            $table->text('instructions'); // freeform instructions shown to the depositor
            $table->string('address')->nullable(); // crypto address, account number, $cashtag, PayPal.me link, etc.
            $table->string('memo')->nullable(); // memo/tag/reference some crypto networks require
            $table->string('qr_code_path')->nullable();
            $table->decimal('min_amount', 20, 2)->default(10);
            $table->decimal('max_amount', 20, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('deposits', function (Blueprint $table) {
            $table->foreignId('payment_method_id')->nullable()->after('network_id')->constrained()->nullOnDelete();
            $table->string('reference_code')->nullable()->after('payment_method_id');
            $table->string('proof_file_path')->nullable()->after('evidence_url');
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->string('payment_method_type')->nullable()->after('network_id'); // crypto, bank_transfer, cashapp, venmo, paypal, other
            $table->text('destination_details')->nullable()->after('address'); // bank account/routing/SWIFT, $cashtag, etc.
        });
    }

    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn(['payment_method_type', 'destination_details']);
        });

        Schema::table('deposits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_method_id');
            $table->dropColumn(['reference_code', 'proof_file_path']);
        });

        Schema::dropIfExists('payment_methods');
    }
};
