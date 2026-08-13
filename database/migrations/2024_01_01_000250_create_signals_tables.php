<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signal_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('risk_level')->default('moderate'); // low, moderate, high
            $table->decimal('min_investment', 20, 2)->default(50);
            $table->decimal('max_investment', 20, 2)->nullable();
            $table->decimal('expected_return_pct', 8, 2)->default(0); // disclosed estimate, not guaranteed
            $table->unsignedInteger('duration_days')->default(30);
            $table->decimal('fee_pct', 8, 4)->default(0);
            $table->string('tracked_asset_symbol')->default('BTC'); // real asset this signal settles against
            $table->string('status')->default('active'); // active, paused, retired
            $table->timestamps();
        });

        Schema::create('signal_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('signal_package_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 20, 2);
            $table->decimal('entry_price', 36, 18)->nullable();
            $table->decimal('exit_price', 36, 18)->nullable();
            $table->decimal('pnl', 20, 2)->default(0);
            $table->string('status')->default('active'); // active, paused, stopped
            $table->timestamp('started_at')->nullable();
            $table->timestamp('stopped_at')->nullable();
            $table->timestamp('unlocks_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signal_subscriptions');
        Schema::dropIfExists('signal_packages');
    }
};
