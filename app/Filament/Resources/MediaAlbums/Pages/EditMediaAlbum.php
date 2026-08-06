<?php

namespace App\Filament\Resources\MediaAlbums\Pages;

use App\Filament\Pages\BaseEditRecord;
use App\Filament\Resources\MediaAlbums\MediaAlbumResource;
use App\Models\MediaItem;
use App\Support\MediaGallerySync;
use Filament\Actions\DeleteAction;

class EditMediaAlbum extends BaseEditRecord
{
    protected static string $resource = MediaAlbumResource::class;

    /** @var array<int, string> */
    protected array $pendingImages = [];

    /** @var array<int, string> */
    protected array $pendingVideos = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        $data['gallery_images'] = $record->mediaItems()
            ->where('type', MediaItem::TYPE_IMAGE)
            ->orderBy('sort_order')
            ->pluck('path')
            ->all();

        $data['gallery_videos'] = $record->mediaItems()
            ->where('type', MediaItem::TYPE_VIDEO)
            ->orderBy('sort_order')
            ->pluck('path')
            ->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->pendingImages = array_values(array_filter((array) ($data['gallery_images'] ?? [])));
        $this->pendingVideos = array_values(array_filter((array) ($data['gallery_videos'] ?? [])));

        unset($data['gallery_images'], $data['gallery_videos']);

        return $data;
    }

    protected function afterSave(): void
    {
        app(MediaGallerySync::class)->syncForAlbum(
            $this->getRecord()->refresh(),
            $this->pendingImages,
            $this->pendingVideos,
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
