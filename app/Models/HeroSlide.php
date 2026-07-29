<?php

namespace App\Models;

use App\Observers\HeroSlideObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[ObservedBy(HeroSlideObserver::class)]
class HeroSlide extends Model
{
    protected $fillable = [
        'headline',
        'kicker',
        'accent_text',
        'subtext',
        'image_path',
        'image_path_tablet',
        'image_path_mobile',
        'background_image_path',
        'thumbnail_image_path',
        'button_text',
        'button_url',
        'show_site_logo',
        'is_active',
        'sort_order',
        'fill_mode',
        'fit_mode',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_site_logo' => 'boolean',
        'render_meta' => 'array',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Ön yüzde kullanılacak görsel adresleri.
     *
     * Üretilmiş cihaz varyantları varsa onlar kullanılır; yoksa yüklenen
     * orijinal dosyaya düşülür. Anahtarlar boş olabilir, çağıran taraf
     * kendi varsayılanını uygular.
     *
     * @return array{mobile: ?string, tablet: ?string, desktop_srcset: ?string, image: ?string}
     */
    public function imageSet(): array
    {
        $url = function (?string $path): ?string {
            return filled($path) ? Storage::url($path) : null;
        };

        $desktopSources = [];

        if (filled($this->rendered_desktop_path)) {
            $desktopSources[] = $url($this->rendered_desktop_path) . ' 1920w';
        }

        if (filled($this->rendered_desktop_2x_path)) {
            $desktopSources[] = $url($this->rendered_desktop_2x_path) . ' 2560w';
        }

        return [
            'mobile' => $url($this->rendered_mobile_path),
            'tablet' => $url($this->rendered_tablet_path),
            'desktop_srcset' => $desktopSources === [] ? null : implode(', ', $desktopSources),
            // <img> etiketi için: WebP desteklemeyen tarayıcılar ve tarayıcı dışı
            // önizlemeler bu adresi kullanır.
            'image' => $url($this->rendered_desktop_fallback_path) ?? $url($this->image_path),
        ];
    }

    /**
     * Üretim sırasında oluşan kalite/dolgu uyarıları.
     *
     * @return array<int, string>
     */
    public function renderWarnings(): array
    {
        return $this->render_meta['warnings'] ?? [];
    }
}
