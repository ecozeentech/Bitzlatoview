<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_bot_trades', function (Blueprint $table) {
            $table->decimal('entry_price', 36, 18)->nullable()->after('side');
            $table->decimal('exit_price', 36, 18)->nullable()->after('entry_price');
            $table->timestamp('closed_at')->nullable()->after('executed_at');
        });
    }

    public function down(): void
    {
        Schema::table('ai_bot_trades', function (Blueprint $table) {
            $table->dropColumn(['entry_price', 'exit_price', 'closed_at']);
        });
    }
};
