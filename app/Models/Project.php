<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'title',
        'title_i18n',
        'slug',
        'description',
        'description_i18n',
        'donation_amount',
        'donation_currency',
        'goal_amount',
        'collected_amount',
        'content',
        'content_i18n',
        'detail_item_1_title',
        'detail_item_1_title_i18n',
        'detail_item_1_text',
        'detail_item_1_text_i18n',
        'detail_item_2_title',
        'detail_item_2_title_i18n',
        'detail_item_2_text',
        'detail_item_2_text_i18n',
        'detail_item_3_title',
        'detail_item_3_title_i18n',
        'detail_item_3_text',
        'detail_item_3_text_i18n',
        'cover_image',
        'gallery_images',
        'gallery_videos',
        'status',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'gallery_images' => 'array',
        'gallery_videos' => 'array',
        'title_i18n' => 'array',
        'description_i18n' => 'array',
        'content_i18n' => 'array',
        'detail_item_1_title_i18n' => 'array',
        'detail_item_1_text_i18n' => 'array',
        'detail_item_2_title_i18n' => 'array',
        'detail_item_2_text_i18n' => 'array',
        'detail_item_3_title_i18n' => 'array',
        'detail_item_3_text_i18n' => 'array',
        'donation_amount' => 'decimal:2',
        'goal_amount' => 'decimal:2',
        'collected_amount' => 'decimal:2',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function mediaItems(): HasMany
    {
        return $this->hasMany(MediaItem::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return array<int, string>
     */
    public function galleryImagePaths(): array
    {
        $paths = $this->mediaItems()
            ->where('type', MediaItem::TYPE_IMAGE)
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
            (array) ($this->gallery_images ?? [])
        )));
    }

    /**
     * @return array<int, string>
     */
    public function galleryVideoPaths(): array
    {
        $paths = $this->mediaItems()
            ->where('type', MediaItem::TYPE_VIDEO)
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
            (array) ($this->gallery_videos ?? [])
        )));
    }

    public function getLocalized(string $field, ?string $fallback = null): ?string
    {
        $locale = app()->getLocale();
        $i18nField = $field . '_i18n';
        $translations = is_array($this->{$i18nField} ?? null) ? $this->{$i18nField} : [];

        $value = $translations[$locale] ?? $translations['tr'] ?? $this->{$field} ?? $fallback;
        $value = is_string($value) ? trim($value) : $value;

        return filled($value) ? (string) $value : $fallback;
    }

    public function getLocalizedDetailItems(): array
    {
        $defaults = [
            ['title' => 'Hizli Mudahale', 'text' => 'Kriz anlarinda hizli mudahale ederek ihtiyac sahiplerine destek sagliyoruz.'],
            ['title' => 'Uzun Vadeli Cozumler', 'text' => 'Surdurulebilir etki icin yerel isbirligi modelleri gelistiriyoruz.'],
            ['title' => 'Toplum Destegi', 'text' => 'Toplum odakli faaliyetlerle kalici fayda uretmeyi hedefliyoruz.'],
        ];

        return [
            [
                'title' => $this->getLocalized('detail_item_1_title', $defaults[0]['title']),
                'text' => $this->getLocalized('detail_item_1_text', $defaults[0]['text']),
            ],
            [
                'title' => $this->getLocalized('detail_item_2_title', $defaults[1]['title']),
                'text' => $this->getLocalized('detail_item_2_text', $defaults[1]['text']),
            ],
            [
                'title' => $this->getLocalized('detail_item_3_title', $defaults[2]['title']),
                'text' => $this->getLocalized('detail_item_3_text', $defaults[2]['text']),
            ],
        ];
    }
}
