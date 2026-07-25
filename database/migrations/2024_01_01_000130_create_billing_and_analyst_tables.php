<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analyst_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('credential')->nullable(); // e.g. "Market Analyst", only "CFA Charterholder" if verified
            $table->boolean('credential_verified')->default(false);
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
        });

        Schema::create('billing_packages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('analyst_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('price', 20, 2);
            $table->string('billing_cycle')->default('monthly'); // monthly, quarterly, yearly
            $table->json('features')->nullable();
            $table->boolean('report_access')->default(false);
            $table->unsignedInteger('consultation_minutes')->default(0);
            $table->text('risk_disclosure')->nullable();
            $table->string('invoice_label');
            $table->string('status')->default('active'); // active, archived
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('billing_package_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('active'); // active, cancelled, expired
            $table->timestamp('started_at');
            $table->timestamp('renews_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 20, 2);
            $table->string('currency', 8)->default('USD');
            $table->string('status')->default('paid'); // paid, pending, refunded, void
            $table->json('line_items')->nullable();
            $table->timestamp('issued_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('billing_packages');
        Schema::dropIfExists('analyst_profiles');
    }
};
