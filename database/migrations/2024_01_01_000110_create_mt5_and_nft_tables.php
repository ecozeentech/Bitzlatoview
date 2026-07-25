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
            $table->string('account_type')->default('demo'); // demo, standard, ecn
            $table->unsignedInteger('leverage')->default(100);
            $table->string('currency', 8)->default('USD');
            $table->text('encrypted_credentials')->nullable();
            $table->string('status')->default('pending'); // pending, connected, disconnected, error
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
        });

        Schema::create('mt5_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mt5_account_id')->constrained()->cascadeOnDelete();
            $table->string('symbol');
            $table->string('side');
            $table->decimal('volume', 10, 2);
            $table->decimal('open_price', 12, 5);
            $table->decimal('current_price', 12, 5)->default(0);
            $table->decimal('pnl', 20, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('mt5_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mt5_account_id')->constrained()->cascadeOnDelete();
            $table->string('status'); // success, failed
            $table->text('message')->nullable();
            $table->timestamp('synced_at');
            $table->timestamps();
        });

        Schema::create('nft_collections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('banner_image')->nullable();
            $table->text('description')->nullable();
            $table->decimal('floor_price', 20, 4)->default(0);
            $table->decimal('volume', 20, 4)->default(0);
            $table->unsignedInteger('owners_count')->default(0);
            $table->unsignedInteger('items_count')->default(0);
            $table->timestamps();
        });

        Schema::create('nft_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nft_collection_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('token_id');
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('price', 20, 4)->nullable();
            $table->boolean('is_listed')->default(false);
            $table->json('rarity')->nullable();
            $table->timestamps();
        });

        Schema::create('nft_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nft_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('price', 20, 4);
            $table->string('status')->default('active'); // active, sold, cancelled
            $table->timestamps();
        });

        Schema::create('nft_bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nft_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bidder_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 20, 4);
            $table->string('status')->default('active'); // active, accepted, rejected, withdrawn
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nft_bids');
        Schema::dropIfExists('nft_listings');
        Schema::dropIfExists('nft_items');
        Schema::dropIfExists('nft_collections');
        Schema::dropIfExists('mt5_sync_logs');
        Schema::dropIfExists('mt5_positions');
        Schema::dropIfExists('mt5_accounts');
    }
};
