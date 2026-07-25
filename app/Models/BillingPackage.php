<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingPackage extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'credential_verified' => 'boolean',
            'report_access' => 'boolean',
        ];
    }
}
