<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('full_legal_name')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->string('country', 2)->nullable();
            $table->string('city')->nullable();
            $table->string('referral_code')->nullable()->unique();
            $table->foreignId('referred_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role')->default('user'); // user, admin, support, compliance
            $table->string('status')->default('active'); // active, suspended
            $table->string('kyc_status')->default('not_started');
            $table->boolean('two_factor_enabled')->default(false);
            $table->text('two_factor_secret')->nullable();
            $table->timestamp('terms_accepted_at')->nullable();
            $table->timestamp('privacy_accepted_at')->nullable();
            $table->timestamp('risk_accepted_at')->nullable();
            $table->timestamp('futures_agreement_at')->nullable();
            $table->boolean('email_marketing_opt_in')->default(true);
            $table->json('preferences')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
        });

        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date_of_birth')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('occupation')->nullable();
            $table->string('trading_experience')->nullable();
            $table->string('tax_residency', 2)->nullable();
            $table->string('tin')->nullable();
            $table->string('source_of_funds')->nullable();
            $table->boolean('is_pep')->default(false);
            $table->text('bio')->nullable();
            $table->string('avatar_path')->nullable();
            $table->timestamps();
        });

        Schema::create('device_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('session_id')->nullable();
            $table->string('device_name')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
        });

        Schema::create('policy_acceptances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('policy_type');
            $table->string('version')->default('1.0');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('kyc_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('in_progress');
            $table->string('legal_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('country', 2)->nullable();
            $table->text('address')->nullable();
            $table->string('id_type')->nullable();
            $table->string('id_number')->nullable();
            $table->string('occupation')->nullable();
            $table->string('source_of_funds')->nullable();
            $table->string('trading_experience')->nullable();
            $table->string('tax_residency', 2)->nullable();
            $table->string('tin')->nullable();
            $table->boolean('is_pep')->default(false);
            $table->boolean('sanctions_check')->default(false);
            $table->unsignedTinyInteger('risk_score')->nullable();
            $table->text('admin_note')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('kyc_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kyc_submission_id')->constrained()->cascadeOnDelete();
            $table->string('document_type'); // government_id, proof_of_address, selfie
            $table->string('file_path');
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['target_type', 'target_id']);
        });

        Schema::create('admin_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->text('note');
            $table->string('visibility')->default('internal');
            $table->timestamps();
        });

        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->boolean('enabled')->default(true);
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('feature_flags');
        Schema::dropIfExists('admin_notes');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('kyc_documents');
        Schema::dropIfExists('kyc_submissions');
        Schema::dropIfExists('policy_acceptances');
        Schema::dropIfExists('device_sessions');
        Schema::dropIfExists('user_profiles');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by');
            $table->dropColumn([
                'full_legal_name', 'phone', 'country', 'city', 'referral_code',
                'role', 'status', 'kyc_status', 'two_factor_enabled', 'two_factor_secret',
                'terms_accepted_at', 'privacy_accepted_at', 'risk_accepted_at',
                'futures_agreement_at', 'email_marketing_opt_in', 'preferences',
                'last_login_at', 'last_login_ip',
            ]);
        });
    }
};
