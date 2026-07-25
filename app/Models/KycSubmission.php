<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KycSubmission extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_pep' => 'boolean',
            'is_sanctioned' => 'boolean',
        ];
    }
}
