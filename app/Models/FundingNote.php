<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FundingNote extends Model
{
    protected $table = 'funding_notes';

    protected $fillable = [
        'notable_type',
        'notable_id',
        'user_note',
        'admin_note',
        'compliance_note',
        'rejection_reason',
        'evidence_url',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

}
