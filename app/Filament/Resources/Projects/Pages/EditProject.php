<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Pages\BaseEditRecord;
use App\Support\MediaGallerySync;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class EditProject extends BaseEditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['title_i18n'] = $this->withTrFallback($data['title_i18n'] ?? [], $data['title'] ?? null);
        $data['description_i18n'] = $this->withTrFallback($data['description_i18n'] ?? [], $data['description'] ?? null);
        $data['content_i18n'] = $this->withTrFallback($data['content_i18n'] ?? [], $data['content'] ?? null);
        $data['detail_item_1_title_i18n'] = $this->withTrFallback($data['detail_item_1_title_i18n'] ?? [], $data['detail_item_1_title'] ?? null);
        $data['detail_item_1_text_i18n'] = $this->withTrFallback($data['detail_item_1_text_i18n'] ?? [], $data['detail_item_1_text'] ?? null);
        $data['detail_item_2_title_i18n'] = $this->withTrFallback($data['detail_item_2_title_i18n'] ?? [], $data['detail_item_2_title'] ?? null);
        $data['detail_item_2_text_i18n'] = $this->withTrFallback($data['detail_item_2_text_i18n'] ?? [], $data['detail_item_2_text'] ?? null);
        $data['detail_item_3_title_i18n'] = $this->withTrFallback($data['detail_item_3_title_i18n'] ?? [], $data['detail_item_3_title'] ?? null);
        $data['detail_item_3_text_i18n'] = $this->withTrFallback($data['detail_item_3_text_i18n'] ?? [], $data['detail_item_3_text'] ?? null);

        $data['gallery_images'] = $this->getRecord()->galleryImagePaths();
        $data['gallery_videos'] = $this->getRecord()->galleryVideoPaths();

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord()->refresh();
        app(MediaGallerySync::class)->syncForProject(
            $record,
            (array) ($record->gallery_images ?? []),
            (array) ($record->gallery_videos ?? []),
        );
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['title_i18n'] = $this->normalizeLocaleArray($data['title_i18n'] ?? []);
        $data['description_i18n'] = $this->normalizeLocaleArray($data['description_i18n'] ?? []);
        $data['content_i18n'] = $this->normalizeLocaleArray($data['content_i18n'] ?? []);
        $data['detail_item_1_title_i18n'] = $this->normalizeLocaleArray($data['detail_item_1_title_i18n'] ?? []);
        $data['detail_item_1_text_i18n'] = $this->normalizeLocaleArray($data['detail_item_1_text_i18n'] ?? []);
        $data['detail_item_2_title_i18n'] = $this->normalizeLocaleArray($data['detail_item_2_title_i18n'] ?? []);
        $data['detail_item_2_text_i18n'] = $this->normalizeLocaleArray($data['detail_item_2_text_i18n'] ?? []);
        $data['detail_item_3_title_i18n'] = $this->normalizeLocaleArray($data['detail_item_3_title_i18n'] ?? []);
        $data['detail_item_3_text_i18n'] = $this->normalizeLocaleArray($data['detail_item_3_text_i18n'] ?? []);

        $data['title'] = Arr::get($data, 'title_i18n.tr');
        $data['description'] = Arr::get($data, 'description_i18n.tr');
        $data['content'] = Arr::get($data, 'content_i18n.tr');
        $data['detail_item_1_title'] = Arr::get($data, 'detail_item_1_title_i18n.tr');
        $data['detail_item_1_text'] = Arr::get($data, 'detail_item_1_text_i18n.tr');
        $data['detail_item_2_title'] = Arr::get($data, 'detail_item_2_title_i18n.tr');
        $data['detail_item_2_text'] = Arr::get($data, 'detail_item_2_text_i18n.tr');
        $data['detail_item_3_title'] = Arr::get($data, 'detail_item_3_title_i18n.tr');
        $data['detail_item_3_text'] = Arr::get($data, 'detail_item_3_text_i18n.tr');

        if (blank($data['slug'] ?? null) && filled($data['title'])) {
            $data['slug'] = Str::slug((string) $data['title']);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    private function normalizeLocaleArray(array $value): array
    {
        return collect($value)
            ->only(['tr', 'en', 'ar'])
            ->map(fn ($v) => is_string($v) ? trim($v) : $v)
            ->all();
    }

    private function withTrFallback(array $value, mixed $trValue): array
    {
        $value = $this->normalizeLocaleArray($value);
        if (! filled($value['tr'] ?? null) && filled($trValue)) {
            $value['tr'] = (string) $trValue;
        }

        return $value;
    }
}
