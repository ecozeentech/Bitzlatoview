<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserProfile extends Model
{
    protected $table = 'user_profiles';

    protected $fillable = [
        'user_id',
        'date_of_birth',
        'address_line1',
        'address_line2',
        'postal_code',
        'occupation',
        'trading_experience',
        'tax_residency',
        'tin',
        'source_of_funds',
        'is_pep',
        'bio',
        'avatar_path',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_pep' => 'boolean',
        ];
    }

}
