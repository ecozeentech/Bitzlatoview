<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KycDocument extends Model
{
    protected $table = 'kyc_documents';

    protected $fillable = [
        'kyc_submission_id',
        'document_type',
        'file_path',
        'status',
    ];

}
