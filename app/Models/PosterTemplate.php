<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class PosterTemplate extends Model
{
    public const TYPE_DONATION = 'donation_poster';

    public const TYPE_THANKS = 'thanks_poster';

    public const TYPES = [
        self::TYPE_DONATION => 'Bağış Afişi',
        self::TYPE_THANKS => 'Teşekkür Afişi',
    ];

    /** Yeni şablon oluştururken seçilebilen türler (bağış afişi kaldırıldı). */
    public const CREATABLE_TYPES = [
        self::TYPE_THANKS => 'Teşekkür Afişi',
    ];

    protected $fillable = [
        'name',
        'type',
        'background_path',
        'canvas_width',
        'canvas_height',
        'layout',
        'thanks_text_template',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'layout' => 'array',
        'canvas_width' => 'integer',
        'canvas_height' => 'integer',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function posters(): HasMany
    {
        return $this->hasMany(PosterDocument::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getBackgroundUrlAttribute(): ?string
    {
        if (! $this->background_path) {
            return null;
        }

        // Göreli yol: görsel her zaman bulunulan domain'den yüklenir (www / non-www CORS uyuşmazlığını önler).
        return '/storage/' . ltrim($this->background_path, '/');
    }

    /**
     * Belirli bir tip için aktif/varsayılan şablonu seçer.
     */
    public static function resolveActive(string $type): ?self
    {
        return static::query()
            ->where('type', $type)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('sort_order')
            ->first();
    }

    protected static function booted(): void
    {
        static::deleting(function (PosterTemplate $template): void {
            if ($template->background_path && Storage::disk('public')->exists($template->background_path)) {
                Storage::disk('public')->delete($template->background_path);
            }
        });
    }
}
