<?php

namespace App\Support;

/**
 * Hero slider cihaz ölçüleri — tek kaynak.
 *
 * Frontend aspect-ratio, admin rehberi ve renderer bu sabitleri kullanır.
 * Yüklenen görsel bu ölçülerle birebir uyumluysa kırpma / boşluk / taşma olmaz.
 */
final class HeroImageSpec
{
    /**
     * @var array<string, array{width: int, height: int, label: string, ratio: string, hint: string, breakpoint: string}>
     */
    public const DEVICES = [
        'desktop' => [
            'width' => 1920,
            'height' => 480,
            'label' => 'Masaüstü',
            'ratio' => '4:1',
            'hint' => 'Kısa yatay banner (Aile ve Nesil tarzı). Gemini’de «1920x480» yazın. Logo/yazı kenardan en az 60–80 px içeride kalsın.',
            'breakpoint' => '1024 px ve üzeri',
        ],
        'tablet' => [
            'width' => 1536,
            'height' => 1024,
            'label' => 'Tablet',
            'ratio' => '3:2',
            'hint' => 'iPad / yatay tablet. Metin okunaklı, ana konu ortada kalsın.',
            'breakpoint' => '768–1023 px',
        ],
        'mobile' => [
            'width' => 1080,
            'height' => 1350,
            'label' => 'Telefon',
            'ratio' => '4:5',
            'hint' => 'Dikey kompozisyon. Yatay masaüstü afişini telefona sıkıştırmayın; logo üstte, metin ortada, foto altta.',
            'breakpoint' => '767 px ve altı',
        ],
    ];

    public static function width(string $device): int
    {
        return self::DEVICES[$device]['width'];
    }

    public static function height(string $device): int
    {
        return self::DEVICES[$device]['height'];
    }

    public static function label(string $device): string
    {
        return self::DEVICES[$device]['label'];
    }

    public static function sizeLabel(string $device): string
    {
        $d = self::DEVICES[$device];

        return sprintf('%d×%d px (%s)', $d['width'], $d['height'], $d['ratio']);
    }

    /**
     * Tailwind arbitrary aspect sınıfları (mobil → tablet → masaüstü).
     */
    public static function frameClasses(): string
    {
        return 'relative w-full overflow-hidden bg-slate-100 aspect-[1080/1350] md:aspect-[1536/1024] lg:aspect-[1920/480]';
    }
}
