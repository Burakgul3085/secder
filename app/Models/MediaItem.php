<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MediaItem extends Model
{
    public const TYPE_IMAGE = 'image';

    public const TYPE_VIDEO = 'video';

    protected $fillable = [
        'project_id',
        'media_album_id',
        'type',
        'path',
        'title',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::saving(function (MediaItem $item): void {
            $hasProject = filled($item->project_id);
            $hasAlbum = filled($item->media_album_id);

            if ($hasProject === $hasAlbum) {
                throw new \InvalidArgumentException('Medya öğesi ya bir faaliyete ya da bir albüme bağlı olmalıdır.');
            }
        });

        static::deleted(function (MediaItem $item): void {
            if (! $item->project_id) {
                return;
            }

            $project = Project::query()->find($item->project_id);
            if ($project) {
                app(\App\Support\MediaGallerySync::class)->refreshProjectJson($project);
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(MediaAlbum::class, 'media_album_id');
    }

    public function isImage(): bool
    {
        return $this->type === self::TYPE_IMAGE;
    }

    public function isVideo(): bool
    {
        return $this->type === self::TYPE_VIDEO;
    }

    public function url(): string
    {
        return asset('storage/' . ltrim((string) $this->path, '/'));
    }

    public function ownerLabel(): string
    {
        if ($this->project) {
            return 'Faaliyet: ' . ($this->project->title ?: ('#' . $this->project_id));
        }

        if ($this->album) {
            return 'Albüm: ' . ($this->album->title ?: ('#' . $this->media_album_id));
        }

        return 'Atanmamış';
    }

    public function destinationKey(): string
    {
        if ($this->project_id) {
            return 'project:' . $this->project_id;
        }

        if ($this->media_album_id) {
            return 'album:' . $this->media_album_id;
        }

        return '';
    }

    public function deleteFileIfOrphan(): void
    {
        $path = ltrim((string) $this->path, '/');
        if ($path === '') {
            return;
        }

        $stillUsed = static::query()
            ->where('path', $path)
            ->where('id', '!=', $this->id)
            ->exists();

        if (! $stillUsed && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
