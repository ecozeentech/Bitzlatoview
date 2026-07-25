<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name', 'email', 'password', 'phone', 'country', 'city', 'date_of_birth',
    'referral_code', 'referred_by', 'terms_accepted_at', 'privacy_accepted_at',
    'risk_disclosure_accepted_at',
])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'terms_accepted_at' => 'datetime',
            'privacy_accepted_at' => 'datetime',
            'risk_disclosure_accepted_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'compliance', 'support']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKycApproved(): bool
    {
        return $this->kyc_status === 'approved';
    }

    public function walletAccounts(): HasMany
    {
        return $this->hasMany(WalletAccount::class);
    }

    public function primaryWallet(): HasOne
    {
        return $this->hasOne(WalletAccount::class)->where('type', WalletAccount::TYPE_PRIMARY);
    }

    public function tradingWallet(): HasOne
    {
        return $this->hasOne(WalletAccount::class)->where('type', WalletAccount::TYPE_TRADING);
    }

    public function investmentWallet(): HasOne
    {
        return $this->hasOne(WalletAccount::class)->where('type', WalletAccount::TYPE_INVESTMENT);
    }

    public function kycSubmissions(): HasMany
    {
        return $this->hasMany(KycSubmission::class);
    }

    public function latestKyc(): HasOne
    {
        return $this->hasOne(KycSubmission::class)->latestOfMany();
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function virtualCards(): HasMany
    {
        return $this->hasMany(VirtualCard::class);
    }

    public function p2pAds(): HasMany
    {
        return $this->hasMany(P2PAd::class);
    }

    public function p2pMerchantProfile(): HasOne
    {
        return $this->hasOne(P2PMerchantProfile::class);
    }

    public function copyAllocations(): HasMany
    {
        return $this->hasMany(CopyAllocation::class);
    }

    public function aiBotAllocations(): HasMany
    {
        return $this->hasMany(AiBotAllocation::class);
    }

    public function miningContracts(): HasMany
    {
        return $this->hasMany(MiningContract::class);
    }

    public function investmentSubscriptions(): HasMany
    {
        return $this->hasMany(InvestmentSubscription::class);
    }

    public function mt5Accounts(): HasMany
    {
        return $this->hasMany(Mt5Account::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function traderProfile(): HasOne
    {
        return $this->hasOne(TraderProfile::class);
    }

    public function isSuspended(): bool
    {
        return $this->status !== 'active';
    }
}
