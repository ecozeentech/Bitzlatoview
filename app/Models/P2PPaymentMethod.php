<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class P2PPaymentMethod extends Model
{
    protected $table = 'p2p_payment_methods';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }
}
