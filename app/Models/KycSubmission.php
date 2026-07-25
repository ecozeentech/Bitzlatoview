<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KycSubmission extends Model
{
    protected $table = 'kyc_submissions';

    protected $fillable = [
        'user_id',
        'status',
        'legal_name',
        'date_of_birth',
        'country',
        'address',
        'id_type',
        'id_number',
        'occupation',
        'source_of_funds',
        'trading_experience',
        'tax_residency',
        'tin',
        'is_pep',
        'sanctions_check',
        'risk_score',
        'admin_note',
        'rejection_reason',
        'reviewed_by',
        'submitted_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_pep' => 'boolean',
            'sanctions_check' => 'boolean',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
