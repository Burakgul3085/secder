<?php

namespace App\Filament\Resources\News\Pages;

use App\Filament\Resources\News\NewsResource;
use App\Filament\Pages\BaseEditRecord;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Arr;

class EditNews extends BaseEditRecord
{
    protected static string $resource = NewsResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $locales = ['tr', 'en', 'ar'];

        $withTrFallback = static function ($translations, ?string $legacyValue = null) use ($locales): array {
            $translations = is_array($translations) ? $translations : [];
            $fallback = filled($translations['tr'] ?? null)
                ? trim((string) $translations['tr'])
                : (filled($legacyValue) ? trim((string) $legacyValue) : null);

            $result = [];
            foreach ($locales as $locale) {
                $raw = $translations[$locale] ?? null;
                $text = is_string($raw) ? trim($raw) : null;
                $result[$locale] = filled($text) ? $text : $fallback;
            }

            return $result;
        };

        $data['title_i18n'] = $withTrFallback(Arr::get($data, 'title_i18n'), Arr::get($data, 'title'));
        $data['summary_i18n'] = $withTrFallback(Arr::get($data, 'summary_i18n'), Arr::get($data, 'summary'));
        $data['content_i18n'] = $withTrFallback(Arr::get($data, 'content_i18n'), Arr::get($data, 'content'));

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
