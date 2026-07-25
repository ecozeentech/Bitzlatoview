<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingPackage extends Model
{
    protected $table = 'billing_packages';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'analyst_name',
        'analyst_credential',
        'credential_verified',
        'price',
        'billing_cycle',
        'features',
        'report_access',
        'consultation_minutes',
        'risk_disclosure',
        'invoice_label',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'credential_verified' => 'boolean',
            'price' => 'decimal:2',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

}
