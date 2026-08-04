<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->json('title_i18n')->nullable()->after('title');
            $table->json('content_i18n')->nullable()->after('content');
        });

        $locales = ['tr', 'en', 'ar', 'ru'];
        $langValue = static function (string $key, string $locale): ?string {
            $translated = trans('app.page.'.$key, [], $locale);
            if (! is_string($translated) || $translated === 'app.page.'.$key) {
                return null;
            }

            return $translated;
        };

        DB::table('pages')->orderBy('id')->get()->each(function ($page) use ($locales, $langValue): void {
            $meta = json_decode($page->page_meta ?? 'null', true);
            $meta = is_array($meta) ? $meta : [];
            $storyItems = json_decode($page->story_items ?? 'null', true);
            $storyItems = is_array($storyItems) ? $storyItems : [];

            $titleI18n = ['tr' => $page->title];
            $contentI18n = ['tr' => $page->content];

            $langKeys = match ($page->slug) {
                'hikayemiz' => ['title' => 'story_page_title', 'content' => 'story_intro'],
                'hakkimizda' => ['title' => 'about_us', 'content' => 'about_content_html'],
                'vizyon-misyon' => ['title' => 'vision_page_title', 'content' => null],
                'baskanin-mesaji' => ['title' => 'president_message_title', 'content' => 'president_message_body'],
                'resmi-bilgiler', 'resmi-belgiler' => ['title' => 'official_page_title', 'content' => null],
                'yonetim' => ['title' => 'management_title', 'content' => 'management_intro'],
                'basin-kiti' => ['title' => 'press_kit_title', 'content' => 'press_intro'],
                'dernek-tuzugu' => ['title' => 'doc_charter_title', 'content' => null],
                'faaliyet-belgesi' => ['title' => 'doc_activity_title', 'content' => null],
                'kurumsal-evrak-arsivi' => ['title' => 'doc_archive_title', 'content' => null],
                'faaliyetler' => ['title' => 'activities_page_title', 'content' => 'activities_intro'],
                default => ['title' => null, 'content' => null],
            };

            foreach (['en', 'ar', 'ru'] as $locale) {
                if ($langKeys['title']) {
                    $translated = $langValue($langKeys['title'], $locale);
                    if ($translated !== null) {
                        $titleI18n[$locale] = $translated;
                    }
                }
                if ($langKeys['content']) {
                    $translated = $langValue($langKeys['content'], $locale);
                    if ($translated !== null) {
                        $contentI18n[$locale] = $translated;
                    }
                }
            }

            if ($page->slug === 'vizyon-misyon') {
                $meta['vision_text_i18n'] = [
                    'tr' => $meta['vision_text'] ?? null,
                    'en' => $langValue('vision_body', 'en'),
                    'ar' => $langValue('vision_body', 'ar'),
                    'ru' => $langValue('vision_body', 'ru'),
                ];
                $meta['mission_text_i18n'] = [
                    'tr' => $meta['mission_text'] ?? null,
                    'en' => $langValue('mission_body', 'en'),
                    'ar' => $langValue('mission_body', 'ar'),
                    'ru' => $langValue('mission_body', 'ru'),
                ];
            }

            if ($page->slug === 'baskanin-mesaji') {
                $meta['signature_title_i18n'] = [
                    'tr' => $meta['signature_title'] ?? null,
                    'en' => $langValue('president_signature_title', 'en'),
                    'ar' => $langValue('president_signature_title', 'ar'),
                    'ru' => $langValue('president_signature_title', 'ru'),
                ];
            }

            if (in_array($page->slug, ['dernek-tuzugu', 'faaliyet-belgesi', 'kurumsal-evrak-arsivi'], true)) {
                $docKey = match ($page->slug) {
                    'dernek-tuzugu' => 'doc_charter_title',
                    'faaliyet-belgesi' => 'doc_activity_title',
                    default => 'doc_archive_title',
                };
                $meta['document_title_i18n'] = [
                    'tr' => $meta['document_title'] ?? null,
                    'en' => $langValue($docKey, 'en'),
                    'ar' => $langValue($docKey, 'ar'),
                    'ru' => $langValue($docKey, 'ru'),
                ];
            }

            $langStoryItems = [];
            foreach (['en', 'ar', 'ru'] as $locale) {
                $items = trans('app.page.story_items', [], $locale);
                $langStoryItems[$locale] = is_array($items) ? array_values($items) : [];
            }

            foreach ($storyItems as $index => &$item) {
                if (! is_array($item)) {
                    continue;
                }
                $item['title_i18n'] = ['tr' => $item['title'] ?? null];
                $item['description_i18n'] = ['tr' => $item['description'] ?? null];
                foreach (['en', 'ar', 'ru'] as $locale) {
                    $langItem = $langStoryItems[$locale][$index] ?? null;
                    if (is_array($langItem)) {
                        $item['title_i18n'][$locale] = $langItem['title'] ?? null;
                        $item['description_i18n'][$locale] = $langItem['description'] ?? null;
                    }
                }
            }
            unset($item);

            if (! empty($meta['press_kit_items']) && is_array($meta['press_kit_items'])) {
                foreach ($meta['press_kit_items'] as &$pressItem) {
                    if (! is_array($pressItem)) {
                        continue;
                    }
                    $pressItem['title_i18n'] = ['tr' => $pressItem['title'] ?? null];
                }
                unset($pressItem);
            }

            if (! empty($meta['management_sections']) && is_array($meta['management_sections'])) {
                foreach ($meta['management_sections'] as &$section) {
                    if (! is_array($section)) {
                        continue;
                    }
                    $section['section_title_i18n'] = ['tr' => $section['section_title'] ?? null];
                    if (! empty($section['members']) && is_array($section['members'])) {
                        foreach ($section['members'] as &$member) {
                            if (! is_array($member)) {
                                continue;
                            }
                            $member['role_i18n'] = ['tr' => $member['role'] ?? null];
                        }
                        unset($member);
                    }
                }
                unset($section);
            }

            $pack = static function (array $map) use ($locales): array {
                $out = [];
                foreach ($locales as $locale) {
                    $out[$locale] = filled($map[$locale] ?? null) ? $map[$locale] : null;
                }

                return $out;
            };

            DB::table('pages')->where('id', $page->id)->update([
                'title_i18n' => json_encode($pack($titleI18n), JSON_UNESCAPED_UNICODE),
                'content_i18n' => json_encode($pack($contentI18n), JSON_UNESCAPED_UNICODE),
                'story_items' => $storyItems !== [] ? json_encode(array_values($storyItems), JSON_UNESCAPED_UNICODE) : $page->story_items,
                'page_meta' => json_encode($meta, JSON_UNESCAPED_UNICODE),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['title_i18n', 'content_i18n']);
        });
    }
};
