<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaAlbum extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function mediaItems(): HasMany
    {
        return $this->hasMany(MediaItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function images(): HasMany
    {
        return $this->mediaItems()->where('type', MediaItem::TYPE_IMAGE);
    }

    public function videos(): HasMany
    {
        return $this->mediaItems()->where('type', MediaItem::TYPE_VIDEO);
    }
}
