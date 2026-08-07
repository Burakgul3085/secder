<?php

namespace App\Filament\Resources\News\Pages;

use App\Filament\Resources\News\NewsResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;

class CreateNews extends CreateRecord
{
    protected static string $resource = NewsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $locales = ['tr', 'en', 'ar'];

        $normalizeLocaleArray = static function ($value) use ($locales): array {
            $value = is_array($value) ? $value : [];
            $normalized = [];

            foreach ($locales as $locale) {
                $raw = $value[$locale] ?? null;
                $text = is_string($raw) ? trim($raw) : null;
                $normalized[$locale] = filled($text) ? $text : null;
            }

            return $normalized;
        };

        $data['title_i18n'] = $normalizeLocaleArray(Arr::get($data, 'title_i18n'));
        $data['summary_i18n'] = $normalizeLocaleArray(Arr::get($data, 'summary_i18n'));
        $data['content_i18n'] = $normalizeLocaleArray(Arr::get($data, 'content_i18n'));

        $data['title'] = $data['title_i18n']['tr'] ?? $data['title'] ?? null;
        $data['summary'] = $data['summary_i18n']['tr'] ?? $data['summary'] ?? null;
        $data['content'] = $data['content_i18n']['tr'] ?? $data['content'] ?? null;

        return $data;
    }
}
