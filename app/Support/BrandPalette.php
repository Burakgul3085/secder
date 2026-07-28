<?php

namespace App\Support;

/**
 * Kurumsal renk paleti — Secder logosundan türetilmiş lacivert-mavi skala.
 *
 * Tek kaynak noktası olarak tutulur; Filament panelleri, grafikler, PDF/Excel
 * çıktıları ve afiş araçları bu değerleri kullanır. Aynı skalanın Tailwind
 * karşılığı `tailwind.config.js` içindedir.
 */
final class BrandPalette
{
    /** @var array<int, string> */
    public const SHADES = [
        50 => '#f5f7fb',
        100 => '#e8edf6',
        200 => '#d1dbec',
        300 => '#aebfda',
        400 => '#8397bd',
        500 => '#5f6f9b',
        600 => '#4d5c83',
        700 => '#3f4c6b',
        800 => '#333c55',
        900 => '#2b3245',
        950 => '#1a1f2c',
    ];

    /** Butonlar ve birincil vurgular. */
    public const PRIMARY = '#4d5c83';

    /** Logodaki ana ton; ikincil vurgu ve grafik serileri. */
    public const ACCENT = '#5f6f9b';

    /** Başlık şeritleri, tablo başlıkları, koyu zeminler. */
    public const DARK = '#333c55';

    /**
     * Filament panellerinin `colors()` yapılandırmasına verilen skala.
     *
     * @return array<int, string>
     */
    public static function shades(): array
    {
        return self::SHADES;
    }

    /**
     * Grafiklerde çok serili gösterim için açıktan koyuya sıralı renk dizisi.
     *
     * @return array<int, string>
     */
    public static function chartSeries(): array
    {
        return [
            self::SHADES[600],
            self::SHADES[400],
            self::SHADES[800],
            self::SHADES[300],
            self::SHADES[700],
            self::SHADES[500],
            self::SHADES[900],
        ];
    }
}
