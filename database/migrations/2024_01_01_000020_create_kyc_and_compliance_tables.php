<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('legal_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('country')->nullable();
            $table->string('address')->nullable();
            $table->string('id_type')->nullable(); // passport, national_id, drivers_license
            $table->string('id_number')->nullable();
            $table->string('government_id_path')->nullable();
            $table->string('proof_of_address_path')->nullable();
            $table->string('selfie_path')->nullable();
            $table->string('source_of_funds')->nullable();
            $table->string('occupation')->nullable();
            $table->string('trading_experience')->nullable();
            $table->string('tax_residency')->nullable();
            $table->string('tin')->nullable();
            $table->boolean('is_pep')->default(false);
            $table->boolean('is_sanctioned')->default(false);
            $table->string('status')->default('not_started');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('kyc_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kyc_submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->string('decision'); // approved, rejected, more_info_required
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('risk_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->default(0);
            $table->string('level')->default('low'); // low, medium, high
            $table->json('factors')->nullable();
            $table->timestamps();
        });

        Schema::create('compliance_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // aml, sanctions, velocity, large_withdrawal, p2p_dispute
            $table->string('severity')->default('medium'); // low, medium, high, critical
            $table->text('details')->nullable();
            $table->string('status')->default('open'); // open, reviewing, resolved, dismissed
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('policy_acceptances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('policy_type'); // terms, privacy, risk_disclosure, futures_agreement
            $table->string('version');
            $table->string('ip_address')->nullable();
            $table->timestamp('accepted_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_acceptances');
        Schema::dropIfExists('compliance_alerts');
        Schema::dropIfExists('risk_scores');
        Schema::dropIfExists('kyc_reviews');
        Schema::dropIfExists('kyc_submissions');
    }
};
