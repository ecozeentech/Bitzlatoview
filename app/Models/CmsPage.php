<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CmsPage extends Model
{
    protected $table = 'cms_pages';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'status',
    ];

}
