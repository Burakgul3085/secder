<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Support\MediaGallerySync;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function afterCreate(): void
    {
        $record = $this->getRecord();
        app(MediaGallerySync::class)->syncForProject(
            $record,
            (array) ($record->gallery_images ?? []),
            (array) ($record->gallery_videos ?? []),
        );
    }

    protected function mutateFormDataBeforeCreate(array $data): array
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

    private function normalizeLocaleArray(array $value): array
    {
        return collect($value)
            ->only(['tr', 'en', 'ar', 'ru'])
            ->map(fn ($v) => is_string($v) ? trim($v) : $v)
            ->all();
    }
}
