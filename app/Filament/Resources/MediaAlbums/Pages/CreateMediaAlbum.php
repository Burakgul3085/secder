<?php

namespace App\Filament\Resources\MediaAlbums\Pages;

use App\Filament\Resources\MediaAlbums\MediaAlbumResource;
use App\Support\MediaGallerySync;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateMediaAlbum extends CreateRecord
{
    protected static string $resource = MediaAlbumResource::class;

    /** @var array<int, string> */
    protected array $pendingImages = [];

    /** @var array<int, string> */
    protected array $pendingVideos = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->pendingImages = array_values(array_filter((array) ($data['gallery_images'] ?? [])));
        $this->pendingVideos = array_values(array_filter((array) ($data['gallery_videos'] ?? [])));

        if (blank($data['slug'] ?? null) && filled($data['title'] ?? null)) {
            $data['slug'] = Str::slug((string) $data['title']);
        }

        unset($data['gallery_images'], $data['gallery_videos']);

        return $data;
    }

    protected function afterCreate(): void
    {
        app(MediaGallerySync::class)->syncForAlbum(
            $this->getRecord(),
            $this->pendingImages,
            $this->pendingVideos,
        );
    }
}
