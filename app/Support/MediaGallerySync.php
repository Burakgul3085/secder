<?php

namespace App\Support;

use App\Models\MediaAlbum;
use App\Models\MediaItem;
use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MediaGallerySync
{
    /**
     * @param  array<int, string|null>  $images
     * @param  array<int, string|null>  $videos
     */
    public function syncForProject(Project $project, array $images, array $videos): void
    {
        $this->syncOwner(
            projectId: $project->id,
            albumId: null,
            images: $images,
            videos: $videos,
        );

        $project->forceFill([
            'gallery_images' => array_values(array_filter(array_map(
                fn ($path) => ltrim((string) $path, '/'),
                $images
            ))),
            'gallery_videos' => array_values(array_filter(array_map(
                fn ($path) => ltrim((string) $path, '/'),
                $videos
            ))),
        ])->saveQuietly();
    }

    /**
     * @param  array<int, string|null>  $images
     * @param  array<int, string|null>  $videos
     */
    public function syncForAlbum(MediaAlbum $album, array $images, array $videos): void
    {
        $this->syncOwner(
            projectId: null,
            albumId: $album->id,
            images: $images,
            videos: $videos,
        );
    }

    /**
     * @param  array<int, string|null>  $images
     * @param  array<int, string|null>  $videos
     */
    private function syncOwner(?int $projectId, ?int $albumId, array $images, array $videos): void
    {
        DB::transaction(function () use ($projectId, $albumId, $images, $videos): void {
            $this->syncType($projectId, $albumId, MediaItem::TYPE_IMAGE, $images);
            $this->syncType($projectId, $albumId, MediaItem::TYPE_VIDEO, $videos);
        });
    }

    /**
     * @param  array<int, string|null>  $paths
     */
    private function syncType(?int $projectId, ?int $albumId, string $type, array $paths): void
    {
        $normalized = collect($paths)
            ->filter(fn ($path) => filled($path))
            ->map(fn ($path) => ltrim((string) $path, '/'))
            ->values();

        $query = MediaItem::query()->where('type', $type);
        if ($projectId) {
            $query->where('project_id', $projectId);
        } else {
            $query->where('media_album_id', $albumId);
        }

        /** @var Collection<int, MediaItem> $existing */
        $existing = $query->orderBy('sort_order')->orderBy('id')->get()->keyBy('path');
        $keptIds = [];

        foreach ($normalized as $index => $path) {
            /** @var MediaItem|null $item */
            $item = $existing->get($path);

            if ($item) {
                $item->update([
                    'project_id' => $projectId,
                    'media_album_id' => $albumId,
                    'sort_order' => $index,
                ]);
                $keptIds[] = $item->id;
                continue;
            }

            $created = MediaItem::query()->create([
                'project_id' => $projectId,
                'media_album_id' => $albumId,
                'type' => $type,
                'path' => $path,
                'sort_order' => $index,
            ]);
            $keptIds[] = $created->id;
        }

        $deleteQuery = MediaItem::query()->where('type', $type);
        if ($projectId) {
            $deleteQuery->where('project_id', $projectId);
        } else {
            $deleteQuery->where('media_album_id', $albumId);
        }
        if ($keptIds !== []) {
            $deleteQuery->whereNotIn('id', $keptIds);
        }

        MediaItem::withoutEvents(function () use ($deleteQuery): void {
            foreach ($deleteQuery->get() as $item) {
                $item->deleteFileIfOrphan();
                $item->delete();
            }
        });
    }

    public function moveToDestination(MediaItem $item, string $destination): void
    {
        [$kind, $id] = array_pad(explode(':', $destination, 2), 2, null);

        if (! in_array($kind, ['project', 'album'], true) || ! ctype_digit((string) $id)) {
            throw new \InvalidArgumentException('Geçersiz taşıma hedefi.');
        }

        $id = (int) $id;
        $oldProjectId = $item->project_id;

        DB::transaction(function () use ($item, $kind, $id, $oldProjectId): void {
            if ($kind === 'project') {
                $project = Project::query()->findOrFail($id);
                $max = (int) MediaItem::query()
                    ->where('project_id', $project->id)
                    ->where('type', $item->type)
                    ->max('sort_order');

                $item->update([
                    'project_id' => $project->id,
                    'media_album_id' => null,
                    'sort_order' => $max + 1,
                ]);

                $this->refreshProjectJson($project);
                if ($oldProjectId && (int) $oldProjectId !== $project->id) {
                    $old = Project::query()->find($oldProjectId);
                    if ($old) {
                        $this->refreshProjectJson($old);
                    }
                }

                return;
            }

            $album = MediaAlbum::query()->findOrFail($id);
            $max = (int) MediaItem::query()
                ->where('media_album_id', $album->id)
                ->where('type', $item->type)
                ->max('sort_order');

            $item->update([
                'project_id' => null,
                'media_album_id' => $album->id,
                'sort_order' => $max + 1,
            ]);

            if ($oldProjectId) {
                $old = Project::query()->find($oldProjectId);
                if ($old) {
                    $this->refreshProjectJson($old);
                }
            }
        });
    }

    public function refreshProjectJson(Project $project): void
    {
        $images = $project->mediaItems()
            ->where('type', MediaItem::TYPE_IMAGE)
            ->orderBy('sort_order')
            ->pluck('path')
            ->all();
        $videos = $project->mediaItems()
            ->where('type', MediaItem::TYPE_VIDEO)
            ->orderBy('sort_order')
            ->pluck('path')
            ->all();

        $project->forceFill([
            'gallery_images' => $images,
            'gallery_videos' => $videos,
        ])->saveQuietly();
    }

    /**
     * @return array<string, string>
     */
    public function destinationOptions(?int $excludeProjectId = null, ?int $excludeAlbumId = null): array
    {
        $options = [];

        Project::query()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title'])
            ->each(function (Project $project) use (&$options, $excludeProjectId): void {
                if ($excludeProjectId && $project->id === $excludeProjectId) {
                    return;
                }
                $options['project:' . $project->id] = 'Faaliyet: ' . ($project->title ?: ('#' . $project->id));
            });

        MediaAlbum::query()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title'])
            ->each(function (MediaAlbum $album) use (&$options, $excludeAlbumId): void {
                if ($excludeAlbumId && $album->id === $excludeAlbumId) {
                    return;
                }
                $options['album:' . $album->id] = 'Albüm: ' . ($album->title ?: ('#' . $album->id));
            });

        return $options;
    }

    public const GALLERY_PER_PAGE = 12;

    /**
     * Başlık kartları için hafif özet — tüm dosya yollarını yüklemez.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function gallerySummaries(): Collection
    {
        $sections = collect();

        $projects = Project::query()
            ->active()
            ->withCount([
                'mediaItems as images_count' => fn ($q) => $q->where('type', MediaItem::TYPE_IMAGE),
                'mediaItems as videos_count' => fn ($q) => $q->where('type', MediaItem::TYPE_VIDEO),
            ])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        foreach ($projects as $project) {
            $imageCount = (int) $project->images_count;
            $videoCount = (int) $project->videos_count;

            if ($imageCount === 0 && is_array($project->gallery_images)) {
                $imageCount = count(array_filter($project->gallery_images));
            }
            if ($videoCount === 0 && is_array($project->gallery_videos)) {
                $videoCount = count(array_filter($project->gallery_videos));
            }

            if ($imageCount === 0 && $videoCount === 0) {
                continue;
            }

            $sections->push([
                'key' => 'project:' . $project->id,
                'kind' => 'project',
                'id' => $project->id,
                'slug' => $project->slug,
                'title' => $project->getLocalized('title', $project->title),
                'image_count' => $imageCount,
                'video_count' => $videoCount,
                'detail_url' => route('activities.show', $project->slug),
                'filter_param' => 'activity',
                'sort_order' => (int) $project->sort_order,
            ]);
        }

        $albums = MediaAlbum::query()
            ->active()
            ->withCount([
                'mediaItems as images_count' => fn ($q) => $q->where('type', MediaItem::TYPE_IMAGE),
                'mediaItems as videos_count' => fn ($q) => $q->where('type', MediaItem::TYPE_VIDEO),
            ])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        foreach ($albums as $album) {
            $imageCount = (int) $album->images_count;
            $videoCount = (int) $album->videos_count;

            if ($imageCount === 0 && $videoCount === 0) {
                continue;
            }

            $sections->push([
                'key' => 'album:' . $album->id,
                'kind' => 'album',
                'id' => $album->id,
                'slug' => $album->slug,
                'title' => $album->title,
                'image_count' => $imageCount,
                'video_count' => $videoCount,
                'detail_url' => null,
                'filter_param' => 'album',
                'sort_order' => (int) $album->sort_order,
            ]);
        }

        return $sections->sortBy([
            ['sort_order', 'asc'],
            ['title', 'asc'],
        ])->values();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findGallerySection(?string $activitySlug, ?string $albumSlug): ?array
    {
        if (! filled($activitySlug) && ! filled($albumSlug)) {
            return null;
        }

        return $this->gallerySummaries()->first(function (array $section) use ($activitySlug, $albumSlug) {
            if (filled($activitySlug) && $section['kind'] === 'project') {
                return $section['slug'] === $activitySlug;
            }
            if (filled($albumSlug) && $section['kind'] === 'album') {
                return $section['slug'] === $albumSlug;
            }

            return false;
        });
    }

    public function paginateSectionImages(array $section, int $perPage = self::GALLERY_PER_PAGE): LengthAwarePaginator
    {
        return $this->paginateSectionMedia($section, MediaItem::TYPE_IMAGE, 'photo_page', $perPage);
    }

    public function paginateSectionVideos(array $section, int $perPage = self::GALLERY_PER_PAGE): LengthAwarePaginator
    {
        return $this->paginateSectionMedia($section, MediaItem::TYPE_VIDEO, 'video_page', $perPage);
    }

    private function paginateSectionMedia(
        array $section,
        string $type,
        string $pageName,
        int $perPage,
    ): LengthAwarePaginator {
        $kind = $section['kind'] ?? '';
        $id = (int) ($section['id'] ?? 0);

        $query = MediaItem::query()
            ->where('type', $type)
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($kind === 'project') {
            $query->where('project_id', $id);
        } elseif ($kind === 'album') {
            $query->where('media_album_id', $id);
        } else {
            return $this->emptyPaginator($pageName, $perPage);
        }

        $count = (clone $query)->count();

        if ($count > 0) {
            return $query
                ->paginate($perPage, ['path', 'type', 'title', 'sort_order', 'id'], $pageName)
                ->withQueryString();
        }

        // Eski JSON yedek (yalnızca faaliyetler)
        if ($kind === 'project') {
            $project = Project::query()->find($id);
            if ($project) {
                $fallback = $type === MediaItem::TYPE_IMAGE
                    ? ($project->gallery_images ?? [])
                    : ($project->gallery_videos ?? []);
                $paths = array_values(array_filter(array_map(
                    fn ($path) => ltrim((string) $path, '/'),
                    is_array($fallback) ? $fallback : []
                )));

                return $this->paginatePathArray($paths, $perPage, $pageName);
            }
        }

        return $this->emptyPaginator($pageName, $perPage);
    }

    /**
     * @param  array<int, string>  $paths
     */
    private function paginatePathArray(array $paths, int $perPage, string $pageName): LengthAwarePaginator
    {
        $page = max(1, (int) Paginator::resolveCurrentPage($pageName));
        $total = count($paths);
        $slice = array_values(array_slice($paths, ($page - 1) * $perPage, $perPage));

        $items = collect($slice)->map(fn (string $path) => (object) [
            'path' => $path,
            'type' => str_contains($pageName, 'video') ? MediaItem::TYPE_VIDEO : MediaItem::TYPE_IMAGE,
            'title' => null,
        ]);

        return (new Paginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
            ]
        ))->withQueryString();
    }

    private function emptyPaginator(string $pageName, int $perPage): LengthAwarePaginator
    {
        return (new Paginator(collect(), 0, $perPage, 1, [
            'path' => request()->url(),
            'pageName' => $pageName,
        ]))->withQueryString();
    }

    /**
     * @deprecated Tam yükleme — yalnızca geriye dönük uyumluluk için.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function gallerySections(?string $activitySlug = null, ?string $albumSlug = null): Collection
    {
        $projectQuery = Project::query()
            ->active()
            ->with(['mediaItems' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')])
            ->orderBy('sort_order')
            ->orderBy('title');

        $albumQuery = MediaAlbum::query()
            ->active()
            ->with(['mediaItems' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')])
            ->orderBy('sort_order')
            ->orderBy('title');

        if (filled($activitySlug)) {
            $projectQuery->where('slug', $activitySlug);
            $albumQuery->whereRaw('1 = 0');
        } elseif (filled($albumSlug)) {
            $albumQuery->where('slug', $albumSlug);
            $projectQuery->whereRaw('1 = 0');
        }

        $sections = collect();

        foreach ($projectQuery->get() as $project) {
            $images = $this->pathsFromItems($project->mediaItems, MediaItem::TYPE_IMAGE, $project->gallery_images);
            $videos = $this->pathsFromItems($project->mediaItems, MediaItem::TYPE_VIDEO, $project->gallery_videos);

            if ($images === [] && $videos === []) {
                continue;
            }

            $sections->push([
                'key' => 'project:' . $project->id,
                'kind' => 'project',
                'slug' => $project->slug,
                'title' => $project->getLocalized('title', $project->title),
                'images' => $images,
                'videos' => $videos,
                'detail_url' => route('activities.show', $project->slug),
                'filter_param' => 'activity',
                'sort_order' => (int) $project->sort_order,
            ]);
        }

        foreach ($albumQuery->get() as $album) {
            $images = $this->pathsFromItems($album->mediaItems, MediaItem::TYPE_IMAGE);
            $videos = $this->pathsFromItems($album->mediaItems, MediaItem::TYPE_VIDEO);

            if ($images === [] && $videos === []) {
                continue;
            }

            $sections->push([
                'key' => 'album:' . $album->id,
                'kind' => 'album',
                'slug' => $album->slug,
                'title' => $album->title,
                'images' => $images,
                'videos' => $videos,
                'detail_url' => null,
                'filter_param' => 'album',
                'sort_order' => (int) $album->sort_order,
            ]);
        }

        return $sections->sortBy([
            ['sort_order', 'asc'],
            ['title', 'asc'],
        ])->values();
    }

    /**
     * @param  Collection<int, MediaItem>|iterable<int, MediaItem>  $items
     * @param  array<int, string>|null  $fallback
     * @return array<int, string>
     */
    private function pathsFromItems(iterable $items, string $type, ?array $fallback = null): array
    {
        $paths = collect($items)
            ->where('type', $type)
            ->pluck('path')
            ->filter()
            ->map(fn ($path) => ltrim((string) $path, '/'))
            ->values()
            ->all();

        if ($paths !== []) {
            return $paths;
        }

        return array_values(array_filter(array_map(
            fn ($path) => ltrim((string) $path, '/'),
            $fallback ?? []
        )));
    }
}
