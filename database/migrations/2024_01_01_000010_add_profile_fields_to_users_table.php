<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('password'); // user, admin, support, compliance
            $table->string('status')->default('active')->after('role'); // active, suspended, banned
            $table->string('phone')->nullable()->after('status');
            $table->string('country')->nullable()->after('phone');
            $table->string('city')->nullable()->after('country');
            $table->date('date_of_birth')->nullable()->after('city');
            $table->string('referral_code')->nullable()->unique()->after('date_of_birth');
            $table->foreignId('referred_by')->nullable()->constrained('users')->nullOnDelete()->after('referral_code');

            $table->string('kyc_status')->default('not_started')->after('referred_by');
            // not_started, in_progress, submitted, under_review, approved, rejected, more_info_required

            $table->boolean('two_factor_enabled')->default(false)->after('kyc_status');
            $table->text('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');

            $table->timestamp('terms_accepted_at')->nullable()->after('two_factor_recovery_codes');
            $table->timestamp('privacy_accepted_at')->nullable()->after('terms_accepted_at');
            $table->timestamp('risk_disclosure_accepted_at')->nullable()->after('privacy_accepted_at');

            $table->timestamp('last_login_at')->nullable()->after('risk_disclosure_accepted_at');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->timestamp('suspended_at')->nullable()->after('last_login_ip');
        });

        Schema::create('device_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('device_name')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_trusted')->default(false);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_sessions');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by');
            $table->dropColumn([
                'role', 'status', 'phone', 'country', 'city', 'date_of_birth', 'referral_code',
                'kyc_status', 'two_factor_enabled', 'two_factor_secret', 'two_factor_recovery_codes',
                'terms_accepted_at', 'privacy_accepted_at', 'risk_disclosure_accepted_at',
                'last_login_at', 'last_login_ip', 'suspended_at',
            ]);
        });
    }
};
