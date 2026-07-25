<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsArticle extends Model
{
    protected $table = 'news_articles';

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'content',
        'source',
        'sentiment',
        'asset_tags',
        'cover_image',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'asset_tags' => 'array',
            'published_at' => 'datetime',
        ];
    }

}
