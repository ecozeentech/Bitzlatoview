<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'full_legal_name',
        'email',
        'phone',
        'country',
        'city',
        'password',
        'referral_code',
        'referred_by',
        'role',
        'status',
        'kyc_status',
        'two_factor_enabled',
        'two_factor_secret',
        'terms_accepted_at',
        'privacy_accepted_at',
        'risk_accepted_at',
        'futures_agreement_at',
        'email_marketing_opt_in',
        'preferences',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'email_marketing_opt_in' => 'boolean',
            'preferences' => 'array',
            'terms_accepted_at' => 'datetime',
            'privacy_accepted_at' => 'datetime',
            'risk_accepted_at' => 'datetime',
            'futures_agreement_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'compliance', 'support'], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function kycApproved(): bool
    {
        return $this->kyc_status === 'approved';
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function walletAccounts(): HasMany
    {
        return $this->hasMany(WalletAccount::class);
    }

    public function primaryWallet(): HasOne
    {
        return $this->hasOne(WalletAccount::class)->where('type', 'PRIMARY');
    }

    public function tradingWallet(): HasOne
    {
        return $this->hasOne(WalletAccount::class)->where('type', 'TRADING');
    }

    public function investmentWallet(): HasOne
    {
        return $this->hasOne(WalletAccount::class)->where('type', 'INVESTMENT');
    }

    public function kycSubmissions(): HasMany
    {
        return $this->hasMany(KycSubmission::class);
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

    public function walletAccount(string $type): ?WalletAccount
    {
        return $this->walletAccounts()->where('type', strtoupper($type))->first();
    }
}
