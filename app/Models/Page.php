<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    public const LOCALES = ['tr', 'en', 'ar', 'ru'];

    protected $fillable = [
        'title',
        'title_i18n',
        'slug',
        'content',
        'content_i18n',
        'story_items',
        'page_meta',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'title_i18n' => 'array',
        'content_i18n' => 'array',
        'story_items' => 'array',
        'page_meta' => 'array',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getLocalized(string $field, ?string $fallback = null): ?string
    {
        $locale = app()->getLocale();
        $i18nField = $field.'_i18n';
        $translations = is_array($this->{$i18nField} ?? null) ? $this->{$i18nField} : [];

        $value = $translations[$locale] ?? $translations['tr'] ?? $this->{$field} ?? $fallback;
        $value = is_string($value) ? trim($value) : $value;

        return filled($value) ? (string) $value : $fallback;
    }

    public function getMetaLocalized(string $key, ?string $fallback = null): ?string
    {
        $meta = is_array($this->page_meta) ? $this->page_meta : [];
        $locale = app()->getLocale();
        $translations = is_array($meta[$key.'_i18n'] ?? null) ? $meta[$key.'_i18n'] : [];

        $value = $translations[$locale]
            ?? $translations['tr']
            ?? ($meta[$key] ?? null)
            ?? $fallback;

        $value = is_string($value) ? trim($value) : $value;

        return filled($value) ? (string) $value : $fallback;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public static function localizedFromItem(array $item, string $field, ?string $fallback = null): ?string
    {
        $locale = app()->getLocale();
        $translations = is_array($item[$field.'_i18n'] ?? null) ? $item[$field.'_i18n'] : [];

        $value = $translations[$locale]
            ?? $translations['tr']
            ?? ($item[$field] ?? null)
            ?? $fallback;

        $value = is_string($value) ? trim($value) : $value;

        return filled($value) ? (string) $value : $fallback;
    }

    /**
     * Yönetim unvanı: önce i18n, yoksa bilinen TR rollerin dil dosyası karşılığı.
     */
    public static function localizeManagementRole(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        if (app()->getLocale() === 'tr') {
            return $value;
        }

        $roleMap = [
            'baskan' => 'app.page.role_president',
            'basakan' => 'app.page.role_president',
            'baskan yardimcisi' => 'app.page.role_vice_president',
            'genel sekreter' => 'app.page.role_secretary_general',
            'sekreter' => 'app.page.role_secretary_general',
            'sayman' => 'app.page.role_treasurer',
            'muhasip' => 'app.page.role_treasurer',
            'uye' => 'app.page.role_member',
            'dernek baskani' => 'app.page.role_president',
        ];

        $normalized = Str::of($value)->lower()->ascii()->value();
        $key = $roleMap[$normalized] ?? null;

        return $key ? __($key) : $value;
    }
}
