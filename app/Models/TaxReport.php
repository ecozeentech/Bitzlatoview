<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxReport extends Model
{
    protected $table = 'tax_reports';

    protected $fillable = [
        'user_id',
        'tax_year',
        'country',
        'cost_basis_method',
        'realized_gains',
        'realized_losses',
        'income_total',
        'fees_paid',
        'status',
        'file_path',
    ];

    protected function casts(): array
    {
        return [
            'realized_gains' => 'decimal:8',
            'realized_losses' => 'decimal:8',
            'income_total' => 'decimal:8',
            'fees_paid' => 'decimal:8',
        ];
    }

}
