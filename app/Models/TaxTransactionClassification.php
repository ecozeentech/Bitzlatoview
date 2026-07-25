<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxTransactionClassification extends Model
{
    protected $table = 'tax_transaction_classifications';

    protected $fillable = [
        'user_id',
        'reference_type',
        'reference_id',
        'classification',
        'cost_basis_method',
        'realized_gain',
        'tax_year',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'realized_gain' => 'decimal:8',
            'metadata' => 'array',
        ];
    }

}
