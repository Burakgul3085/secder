<?php

namespace App\Support;

use Illuminate\Support\Arr;

class PageI18n
{
    public const LOCALES = ['tr', 'en', 'ar', 'ru'];

    public static function withTrFallback($translations, ?string $legacyValue = null): array
    {
        $translations = is_array($translations) ? $translations : [];
        $fallback = filled($translations['tr'] ?? null)
            ? trim((string) $translations['tr'])
            : (filled($legacyValue) ? trim((string) $legacyValue) : null);

        $result = [];
        foreach (self::LOCALES as $locale) {
            $raw = $translations[$locale] ?? null;
            $text = is_string($raw) ? trim($raw) : null;
            $result[$locale] = filled($text) ? $text : $fallback;
        }

        return $result;
    }

    public static function normalize($value): array
    {
        $value = is_array($value) ? $value : [];
        $normalized = [];

        foreach (self::LOCALES as $locale) {
            $raw = $value[$locale] ?? null;
            $text = is_string($raw) ? trim($raw) : null;
            $normalized[$locale] = filled($text) ? $text : null;
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function hydrateForm(array $data): array
    {
        $data['title_i18n'] = self::withTrFallback(Arr::get($data, 'title_i18n'), Arr::get($data, 'title'));
        $data['content_i18n'] = self::withTrFallback(Arr::get($data, 'content_i18n'), Arr::get($data, 'content'));

        $meta = is_array(Arr::get($data, 'page_meta')) ? $data['page_meta'] : [];

        foreach (['vision_text', 'mission_text', 'signature_title', 'document_title'] as $key) {
            $meta[$key.'_i18n'] = self::withTrFallback($meta[$key.'_i18n'] ?? null, $meta[$key] ?? null);
        }

        if (! empty($meta['press_kit_items']) && is_array($meta['press_kit_items'])) {
            foreach ($meta['press_kit_items'] as &$item) {
                if (! is_array($item)) {
                    continue;
                }
                $item['title_i18n'] = self::withTrFallback($item['title_i18n'] ?? null, $item['title'] ?? null);
            }
            unset($item);
        }

        if (! empty($meta['management_sections']) && is_array($meta['management_sections'])) {
            foreach ($meta['management_sections'] as &$section) {
                if (! is_array($section)) {
                    continue;
                }
                $section['section_title_i18n'] = self::withTrFallback(
                    $section['section_title_i18n'] ?? null,
                    $section['section_title'] ?? null
                );
                if (! empty($section['members']) && is_array($section['members'])) {
                    foreach ($section['members'] as &$member) {
                        if (! is_array($member)) {
                            continue;
                        }
                        $member['role_i18n'] = self::withTrFallback($member['role_i18n'] ?? null, $member['role'] ?? null);
                    }
                    unset($member);
                }
            }
            unset($section);
        }

        $data['page_meta'] = $meta;

        $storyItems = is_array(Arr::get($data, 'story_items')) ? $data['story_items'] : [];
        foreach ($storyItems as &$item) {
            if (! is_array($item)) {
                continue;
            }
            $item['title_i18n'] = self::withTrFallback($item['title_i18n'] ?? null, $item['title'] ?? null);
            $item['description_i18n'] = self::withTrFallback($item['description_i18n'] ?? null, $item['description'] ?? null);
        }
        unset($item);
        $data['story_items'] = $storyItems;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function prepareForSave(array $data): array
    {
        $data['title_i18n'] = self::normalize(Arr::get($data, 'title_i18n'));
        $data['content_i18n'] = self::normalize(Arr::get($data, 'content_i18n'));
        $data['title'] = $data['title_i18n']['tr'] ?? $data['title'] ?? null;
        $data['content'] = $data['content_i18n']['tr'] ?? $data['content'] ?? null;

        $meta = is_array(Arr::get($data, 'page_meta')) ? $data['page_meta'] : [];

        foreach (['vision_text', 'mission_text', 'signature_title', 'document_title'] as $key) {
            $meta[$key.'_i18n'] = self::normalize($meta[$key.'_i18n'] ?? null);
            $meta[$key] = $meta[$key.'_i18n']['tr'] ?? $meta[$key] ?? null;
        }

        if (! empty($meta['press_kit_items']) && is_array($meta['press_kit_items'])) {
            foreach ($meta['press_kit_items'] as &$item) {
                if (! is_array($item)) {
                    continue;
                }
                $item['title_i18n'] = self::normalize($item['title_i18n'] ?? null);
                $item['title'] = $item['title_i18n']['tr'] ?? $item['title'] ?? null;
            }
            unset($item);
        }

        if (! empty($meta['management_sections']) && is_array($meta['management_sections'])) {
            foreach ($meta['management_sections'] as &$section) {
                if (! is_array($section)) {
                    continue;
                }
                $section['section_title_i18n'] = self::normalize($section['section_title_i18n'] ?? null);
                $section['section_title'] = $section['section_title_i18n']['tr'] ?? $section['section_title'] ?? null;
                if (! empty($section['members']) && is_array($section['members'])) {
                    foreach ($section['members'] as &$member) {
                        if (! is_array($member)) {
                            continue;
                        }
                        $member['role_i18n'] = self::normalize($member['role_i18n'] ?? null);
                        $member['role'] = $member['role_i18n']['tr'] ?? $member['role'] ?? null;
                    }
                    unset($member);
                }
            }
            unset($section);
        }

        $data['page_meta'] = $meta;

        $storyItems = is_array(Arr::get($data, 'story_items')) ? $data['story_items'] : [];
        foreach ($storyItems as &$item) {
            if (! is_array($item)) {
                continue;
            }
            $item['title_i18n'] = self::normalize($item['title_i18n'] ?? null);
            $item['description_i18n'] = self::normalize($item['description_i18n'] ?? null);
            $item['title'] = $item['title_i18n']['tr'] ?? $item['title'] ?? null;
            $item['description'] = $item['description_i18n']['tr'] ?? $item['description'] ?? null;
        }
        unset($item);
        $data['story_items'] = $storyItems;

        return $data;
    }
}
