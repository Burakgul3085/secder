<?php

namespace App\Support;

use App\Models\HeroSlide;
use GdImage;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Hero slider görsellerini cihaz bandlarına oturtan üretim servisi.
 *
 * Ölçüler HeroImageSpec'ten gelir. Admin doğru boyutta yüklerse cover ile
 * kırpma olmaz (oran birebir). Yanlış oranda cover merkezden kırpar ve uyarır.
 */
class HeroImageRenderer
{
    /** Algoritma sürümü. Değişirse imzalar geçersizleşir ve tüm görseller yeniden üretilir. */
    public const VERSION = 8;

    /** Panelde seçilebilen dolgu yöntemleri. */
    public const FILL_MODES = [
        'auto' => 'Otomatik (görseli analiz edip en uygununu seçer)',
        'blur' => 'Bulanık görsel zemin',
        'gradient' => 'Kurumsal renk geçişi',
        'mirror' => 'Kenar uzatma (aynalama)',
    ];

    /** Görselin banda yerleşme biçimi. */
    public const FIT_MODES = [
        'cover' => 'Alanı doldur (önerilen — ölçülere uygun yüklemede kırpma olmaz)',
        'contain' => 'Görselin tamamı görünsün (oran uymazsa kenarda dolgu kalır)',
        'smart' => 'Akıllı (eski; cover ile aynı)',
    ];

    /**
     * Doldurma modunda kaynak görselin en fazla kırpılabilen oranı.
     * Masaüstü bandı çok geniş olduğu için orada daha esnek davranılır.
     * Sınır aşılırsa güvenlik için "tamamını göster" davranışına dönülür.
     */
    private const MAX_CROP_RATIO = [
        'desktop' => 0.60,
        'tablet' => 0.40,
        'mobile' => 0.40,
    ];

    /** Bellek koruması: bu piksel sayısını aşan kaynaklar reddedilir (~30MP). */
    private const MAX_SOURCE_PIXELS = 30_000_000;

    /** Kaynak görsel bu katsayıdan fazla büyütülmez; fazlası bulanıklaşmaya yol açar. */
    private const MAX_UPSCALE = 2.0;

    /** Bu katsayının üzerindeki büyütmeler için panelde kalite uyarısı gösterilir. */
    private const WARN_UPSCALE = 1.35;

    /** Retina gibi opsiyonel varyantlar, kaynak yetmiyorsa hiç üretilmez. */
    private const OPTIONAL_MAX_UPSCALE = 1.15;

    /** Üretilen dosyaların saklandığı dizin (public disk). */
    private const OUTPUT_DIRECTORY = 'hero/rendered';

    /**
     * Üretilecek varyantlar.
     *
     * @var array<string, array{column: string, source: string, device: string, width: int, height: int, format: string, optional: bool, label: string}>
     */
    private const VARIANTS = [
        'desktop' => [
            'column' => 'rendered_desktop_path',
            'source' => 'desktop',
            'device' => 'desktop',
            'width' => 1920,
            'height' => 480,
            'format' => 'webp',
            'optional' => false,
            'label' => 'Masaüstü',
        ],
        'desktop_2x' => [
            'column' => 'rendered_desktop_2x_path',
            'source' => 'desktop',
            'device' => 'desktop',
            'width' => 2560,
            'height' => 640,
            'format' => 'webp',
            'optional' => true,
            'label' => 'Masaüstü (retina)',
        ],
        'desktop_fallback' => [
            'column' => 'rendered_desktop_fallback_path',
            'source' => 'desktop',
            'device' => 'desktop',
            'width' => 1920,
            'height' => 480,
            'format' => 'jpg',
            'optional' => false,
            'label' => 'Masaüstü (JPG yedek)',
        ],
        'tablet' => [
            'column' => 'rendered_tablet_path',
            'source' => 'tablet',
            'device' => 'tablet',
            'width' => 1536,
            'height' => 1024,
            'format' => 'webp',
            'optional' => false,
            'label' => 'Tablet',
        ],
        'mobile' => [
            'column' => 'rendered_mobile_path',
            'source' => 'mobile',
            'device' => 'mobile',
            'width' => 1080,
            'height' => 1350,
            'format' => 'webp',
            'optional' => false,
            'label' => 'Telefon',
        ],
    ];

    /**
     * Slaytın görsellerini üretir.
     *
     * @return bool Üretim yapıldıysa true, imza değişmediği için atlandıysa false.
     */
    public function render(HeroSlide $slide, bool $force = false): bool
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('PHP GD eklentisi kapalı; hero görselleri üretilemiyor.');
        }

        $sources = $this->sources($slide);

        if ($sources['desktop'] === null) {
            $this->purge($slide, clearColumns: true);

            return false;
        }

        $signature = $this->signature($slide, $sources);

        if (! $force && $slide->render_signature === $signature && $this->allVariantsExist($slide)) {
            return false;
        }

        $previousFiles = $this->renderedPaths($slide);

        /** @var array<string, GdImage> $loaded */
        $loaded = [];
        $updates = [];
        $variantMeta = [];
        $warnings = [];

        try {
            foreach (self::VARIANTS as $key => $variant) {
                $relativeSource = $sources[$variant['source']];

                if ($relativeSource === null) {
                    $updates[$variant['column']] = null;

                    continue;
                }

                if (! isset($loaded[$relativeSource])) {
                    $loaded[$relativeSource] = $this->loadSource($relativeSource);
                }

                $source = $loaded[$relativeSource];
                $sourceWidth = imagesx($source);
                $sourceHeight = imagesy($source);

                $requiredScale = min(
                    $variant['width'] / $sourceWidth,
                    $variant['height'] / $sourceHeight
                );

                // Retina varyantı için kaynak yetersizse yapay büyütme yapmak yerine atla.
                if ($variant['optional'] && $requiredScale > self::OPTIONAL_MAX_UPSCALE) {
                    $updates[$variant['column']] = null;

                    continue;
                }

                $requestedFit = $this->resolveFit('cover', $variant['device']);

                $composed = $this->compose(
                    $source,
                    $variant['width'],
                    $variant['height'],
                    $slide->fill_mode ?: 'auto',
                    $requestedFit,
                    $variant['device']
                );

                $relativeTarget = sprintf(
                    '%s/%d-%s-%s.%s',
                    self::OUTPUT_DIRECTORY,
                    $slide->getKey(),
                    str_replace('_', '-', $key),
                    substr($signature, 0, 10),
                    $variant['format']
                );

                $this->store($composed['canvas'], $relativeTarget, $variant['format']);
                imagedestroy($composed['canvas']);

                $updates[$variant['column']] = $relativeTarget;

                $variantMeta[$key] = [
                    'label' => $variant['label'],
                    'width' => $variant['width'],
                    'height' => $variant['height'],
                    'fit' => $composed['fit'],
                    'fill' => $composed['fill'],
                    'fill_ratio' => round($composed['fill_ratio'], 4),
                    'crop_ratio' => round($composed['crop_ratio'], 4),
                    'scale' => round($composed['scale'], 3),
                ];

                if ($requestedFit === 'cover' && $composed['fit'] === 'contain') {
                    $warnings[] = sprintf(
                        '%s bandını kırpmadan doldurmak için görselin oranı çok farklı; görselin tamamı gösterildi. '
                        . 'Kenarsız bir görünüm için bu cihaza %s oranında görsel yükleyin.',
                        $variant['label'],
                        $this->ratioLabel($variant['width'], $variant['height'])
                    );
                } elseif ($composed['fill_ratio'] >= 0.35) {
                    $warnings[] = sprintf(
                        '%s bandının %%%d\'i dolgu ile tamamlandı. Alanın tamamen dolması için bu cihaza %s oranında ayrı bir görsel yükleyebilirsiniz.',
                        $variant['label'],
                        (int) round($composed['fill_ratio'] * 100),
                        $this->ratioLabel($variant['width'], $variant['height'])
                    );
                }

                if ($composed['scale'] > self::WARN_UPSCALE) {
                    $warnings[] = sprintf(
                        '%s bandı için görsel %.1f kat büyütüldü; keskinlik için en az %d px genişliğinde yükleyin.',
                        $variant['label'],
                        $composed['scale'],
                        $variant['width']
                    );
                }
            }
        } finally {
            foreach ($loaded as $image) {
                imagedestroy($image);
            }
        }

        $slide->forceFill($updates + [
            'render_signature' => $signature,
            'render_meta' => [
                'version' => self::VERSION,
                'rendered_at' => now()->toIso8601String(),
                'fill_mode' => $slide->fill_mode ?: 'auto',
                'fit_mode' => $slide->fit_mode ?: 'cover',
                'variants' => $variantMeta,
                'warnings' => array_values(array_unique($warnings)),
            ],
        ])->saveQuietly();

        // Yeni dosyalar yazıldıktan sonra eski sürümleri temizle.
        $this->deleteFiles(array_diff($previousFiles, array_filter($updates)));

        return true;
    }

    /**
     * Slayta ait üretilmiş dosyaları siler.
     */
    public function purge(HeroSlide $slide, bool $clearColumns = false): void
    {
        $this->deleteFiles($this->renderedPaths($slide));

        if (! $clearColumns) {
            return;
        }

        $columns = array_column(self::VARIANTS, 'column');

        $slide->forceFill(
            array_fill_keys($columns, null) + ['render_signature' => null, 'render_meta' => null]
        )->saveQuietly();
    }

    /**
     * Panelde gösterilecek hedef ölçü listesi.
     *
     * @return array<int, array{label: string, width: int, height: int, ratio: string}>
     */
    public function targets(): array
    {
        $targets = [];

        foreach (self::VARIANTS as $variant) {
            if ($variant['optional'] || $variant['format'] === 'jpg') {
                continue;
            }

            $targets[] = [
                'label' => $variant['label'],
                'width' => $variant['width'],
                'height' => $variant['height'],
                'ratio' => $this->ratioLabel($variant['width'], $variant['height']),
            ];
        }

        return $targets;
    }

    /**
     * Her varyantın kaynak dosyası. Cihaza özel görsel yoksa bir üst kaynağa düşer.
     *
     * @return array{desktop: ?string, tablet: ?string, mobile: ?string}
     */
    private function sources(HeroSlide $slide): array
    {
        $disk = Storage::disk('public');
        $exists = fn (?string $path): ?string => $path && $disk->exists($path) ? $path : null;

        $desktop = $exists($slide->image_path);
        $tablet = $exists($slide->image_path_tablet) ?? $desktop;
        $mobile = $exists($slide->image_path_mobile) ?? $tablet;

        return [
            'desktop' => $desktop,
            'tablet' => $tablet,
            'mobile' => $mobile,
        ];
    }

    /**
     * Kaynak dosyalar + ayarlar imzası. Değişmediyse yeniden üretim gerekmez.
     *
     * @param  array{desktop: ?string, tablet: ?string, mobile: ?string}  $sources
     */
    private function signature(HeroSlide $slide, array $sources): string
    {
        $disk = Storage::disk('public');
        $parts = [
            'v' . self::VERSION,
            'fill:' . ($slide->fill_mode ?: 'auto'),
            'fit:' . ($slide->fit_mode ?: 'cover'),
        ];

        foreach ($sources as $key => $path) {
            if ($path === null) {
                $parts[] = $key . ':-';

                continue;
            }

            $parts[] = sprintf('%s:%s:%d:%d', $key, $path, $disk->size($path), $disk->lastModified($path));
        }

        return hash('sha256', implode('|', $parts));
    }

    /**
     * @return array<int, string>
     */
    private function renderedPaths(HeroSlide $slide): array
    {
        $paths = [];

        foreach (self::VARIANTS as $variant) {
            $value = $slide->getAttribute($variant['column']);

            if (filled($value)) {
                $paths[] = $value;
            }
        }

        return array_values(array_unique($paths));
    }

    private function allVariantsExist(HeroSlide $slide): bool
    {
        $disk = Storage::disk('public');

        foreach (self::VARIANTS as $variant) {
            if ($variant['optional']) {
                continue;
            }

            $value = $slide->getAttribute($variant['column']);

            if (blank($value) || ! $disk->exists($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  iterable<int, string>  $paths
     */
    private function deleteFiles(iterable $paths): void
    {
        $disk = Storage::disk('public');

        foreach ($paths as $path) {
            if (filled($path) && str_starts_with($path, self::OUTPUT_DIRECTORY) && $disk->exists($path)) {
                $disk->delete($path);
            }
        }
    }

    /**
     * Kaynak görseli GD kaynağına çevirir ve EXIF yönünü düzeltir.
     */
    private function loadSource(string $relativePath): GdImage
    {
        $absolute = Storage::disk('public')->path($relativePath);
        $info = @getimagesize($absolute);

        if ($info === false) {
            throw new RuntimeException("Görsel okunamadı: {$relativePath}");
        }

        [$width, $height, $type] = $info;

        if ($width * $height > self::MAX_SOURCE_PIXELS) {
            throw new RuntimeException(
                'Görselin çözünürlüğü çok yüksek (' . $width . 'x' . $height . '). Lütfen 30 megapikselin altında bir dosya yükleyin.'
            );
        }

        $image = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($absolute),
            IMAGETYPE_PNG => @imagecreatefrompng($absolute),
            IMAGETYPE_WEBP => @imagecreatefromwebp($absolute),
            IMAGETYPE_GIF => @imagecreatefromgif($absolute),
            default => null,
        };

        if (! $image instanceof GdImage) {
            throw new RuntimeException('Desteklenmeyen görsel biçimi. JPG, PNG veya WebP yükleyin.');
        }

        return $this->applyExifOrientation($image, $absolute, $type);
    }

    private function applyExifOrientation(GdImage $image, string $absolute, int $type): GdImage
    {
        if ($type !== IMAGETYPE_JPEG || ! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($absolute);
        $orientation = (int) ($exif['Orientation'] ?? 1);

        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => null,
        };

        if (! $rotated instanceof GdImage) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }

    /**
     * Kaynak görseli hedef banda oturtur.
     *
     * Doldurma (cover) modunda görsel merkezden kırpılarak alanı tamamen kaplar;
     * tamamını göster (contain) modunda kırpılmaz, artan alan dolguyla tamamlanır.
     *
     * @return array{canvas: GdImage, fit: string, fill: ?string, fill_ratio: float, crop_ratio: float, scale: float}
     */
    private function compose(GdImage $source, int $width, int $height, string $mode, string $fit, string $device): array
    {
        if ($fit === 'cover') {
            $covered = $this->composeCover($source, $width, $height, $device);

            if ($covered !== null) {
                return $covered;
            }

            // Kırpma güvenlik sınırını aşıyor: görseli kesmek yerine tamamını göster.
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, true);
        imagesavealpha($canvas, false);

        // contain: görselin tamamı görünür, kırpma ve deformasyon olmaz.
        $scale = min($width / $sourceWidth, $height / $sourceHeight, self::MAX_UPSCALE);
        $drawWidth = max(1, (int) round($sourceWidth * $scale));
        $drawHeight = max(1, (int) round($sourceHeight * $scale));
        $drawX = (int) round(($width - $drawWidth) / 2);
        $drawY = (int) round(($height - $drawHeight) / 2);

        $fill = $mode === 'auto' ? $this->detectFillMode($source) : $mode;

        if ($fill === 'gradient') {
            $this->drawGradient($canvas, $source, $width, $height, $drawX > 0);
        } else {
            // Bulanık zemin taban katman: köşeler dahil tüm alanı kapatır.
            $this->drawBlurredBase($canvas, $source, $width, $height);
        }

        if ($fill === 'mirror') {
            $this->drawMirroredBands($canvas, $source, $width, $height, $drawX, $drawY, $drawWidth, $drawHeight);
        }

        imagecopyresampled(
            $canvas,
            $source,
            $drawX,
            $drawY,
            0,
            0,
            $drawWidth,
            $drawHeight,
            $sourceWidth,
            $sourceHeight
        );

        $this->softenSeams($canvas, $width, $height, $drawX, $drawY, $drawWidth, $drawHeight);

        return [
            'canvas' => $canvas,
            'fit' => 'contain',
            'fill' => $fill,
            'fill_ratio' => 1 - (($drawWidth * $drawHeight) / ($width * $height)),
            'crop_ratio' => 0.0,
            'scale' => $scale,
        ];
    }

    /**
     * Alanı tamamen kaplayan yerleşim: görsel merkezden kırpılır, dolgu gerekmez.
     * Kırpma oranı güvenlik sınırını aşarsa null döner.
     *
     * @return array{canvas: GdImage, fit: string, fill: ?string, fill_ratio: float, crop_ratio: float, scale: float}|null
     */
    private function composeCover(GdImage $source, int $width, int $height, string $device): ?array
    {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        $targetRatio = $width / $height;
        $sourceRatio = $sourceWidth / $sourceHeight;

        // Hedef orana uyan, kaynağın içindeki en büyük merkezî alan.
        if ($sourceRatio > $targetRatio) {
            $cropHeight = $sourceHeight;
            $cropWidth = (int) round($sourceHeight * $targetRatio);
        } else {
            $cropWidth = $sourceWidth;
            $cropHeight = (int) round($sourceWidth / $targetRatio);
        }

        $cropRatio = 1 - (($cropWidth * $cropHeight) / ($sourceWidth * $sourceHeight));

        if ($cropRatio > (self::MAX_CROP_RATIO[$device] ?? 0.40)) {
            return null;
        }

        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, true);
        imagesavealpha($canvas, false);

        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            (int) round(($sourceWidth - $cropWidth) / 2),
            (int) round(($sourceHeight - $cropHeight) / 2),
            $width,
            $height,
            $cropWidth,
            $cropHeight
        );

        return [
            'canvas' => $canvas,
            'fit' => 'cover',
            'fill' => null,
            'fill_ratio' => 0.0,
            'crop_ratio' => $cropRatio,
            'scale' => $width / max(1, $cropWidth),
        ];
    }

    /**
     * Slaytın yerleşim tercihini cihaz bazına indirger.
     *
     * Akıllı modda masaüstü bandı geniş olduğu için alanı doldurmak daha iyi
     * durur; tablet ve telefonda görselin kenarları kesilmesin diye tamamı gösterilir.
     */
    private function resolveFit(string $fitMode, string $device): string
    {
        return match ($fitMode) {
            'cover' => 'cover',
            'contain' => 'contain',
            // Yeni sistemde ölçüler birebir: cover = doğru yüklemede sıfır kırpma.
            default => 'cover',
        };
    }

    /**
     * Görseli analiz ederek en uygun dolgu yöntemini seçer.
     *
     * Az renkli/grafik ağırlıklı görsellerde renk geçişi, kenarları homojen
     * fotoğraflarda aynalama, hareketli fotoğraflarda bulanık zemin kazanır.
     */
    private function detectFillMode(GdImage $source): string
    {
        $stats = $this->analyseSource($source);

        if ($stats['unique_colors'] <= 120) {
            return 'gradient';
        }

        if ($stats['edge_variance'] <= 350.0) {
            return 'mirror';
        }

        return 'blur';
    }

    /**
     * @return array{unique_colors: int, edge_variance: float}
     */
    private function analyseSource(GdImage $source): array
    {
        $size = 48;
        $thumb = imagecreatetruecolor($size, $size);
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $size, $size, imagesx($source), imagesy($source));

        $buckets = [];
        $edgeLuminance = [];

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $rgb = imagecolorat($thumb, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                // 4 bitlik kovalara indirgeyip renk zenginliğini ölçüyoruz.
                $buckets[(($r >> 4) << 8) | (($g >> 4) << 4) | ($b >> 4)] = true;

                $isEdge = $y < 3 || $y >= $size - 3 || $x < 3 || $x >= $size - 3;

                if ($isEdge) {
                    $edgeLuminance[] = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
                }
            }
        }

        imagedestroy($thumb);

        return [
            'unique_colors' => count($buckets),
            'edge_variance' => $this->variance($edgeLuminance),
        ];
    }

    /**
     * @param  array<int, float>  $values
     */
    private function variance(array $values): float
    {
        $count = count($values);

        if ($count < 2) {
            return 0.0;
        }

        $mean = array_sum($values) / $count;
        $sum = 0.0;

        foreach ($values as $value) {
            $sum += ($value - $mean) ** 2;
        }

        return $sum / $count;
    }

    /**
     * Görselin `cover` kırpılmış küçük kopyası bulanıklaştırılıp tuvale büyütülür.
     * Küçükte bulanıklaştırıp büyütmek hem hızlı hem çok yumuşak sonuç verir.
     */
    private function drawBlurredBase(GdImage $canvas, GdImage $source, int $width, int $height): void
    {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        $smallWidth = 160;
        $smallHeight = max(12, (int) round($smallWidth * $height / $width));

        // Hedef oranına göre merkezden kırpılmış alan (yalnızca zemin katmanı için).
        $coverScale = max($smallWidth / $sourceWidth, $smallHeight / $sourceHeight);
        $cropWidth = min($sourceWidth, (int) round($smallWidth / $coverScale));
        $cropHeight = min($sourceHeight, (int) round($smallHeight / $coverScale));
        $cropX = (int) round(($sourceWidth - $cropWidth) / 2);
        $cropY = (int) round(($sourceHeight - $cropHeight) / 2);

        $small = imagecreatetruecolor($smallWidth, $smallHeight);
        imagecopyresampled($small, $source, 0, 0, $cropX, $cropY, $smallWidth, $smallHeight, $cropWidth, $cropHeight);

        for ($pass = 0; $pass < 5; $pass++) {
            imagefilter($small, IMG_FILTER_GAUSSIAN_BLUR);
        }

        // İki aşamalı büyütme: ara ölçekte tekrar bulanıklaştırmak bantlanmayı
        // yok eder, tek adımda büyütmeye göre çok daha yumuşak bir zemin verir.
        $midWidth = max($smallWidth, (int) round($width / 4));
        $midHeight = max($smallHeight, (int) round($height / 4));

        $mid = imagecreatetruecolor($midWidth, $midHeight);
        imagecopyresampled($mid, $small, 0, 0, 0, 0, $midWidth, $midHeight, $smallWidth, $smallHeight);
        imagedestroy($small);

        for ($pass = 0; $pass < 3; $pass++) {
            imagefilter($mid, IMG_FILTER_GAUSSIAN_BLUR);
        }

        imagecopyresampled($canvas, $mid, 0, 0, 0, 0, $width, $height, $midWidth, $midHeight);
        imagedestroy($mid);

        // Zemin öne çıkmasın: net görsel ayrışsın diye hafifçe koyulaştırılır.
        imagefilter($canvas, IMG_FILTER_BRIGHTNESS, -14);
    }

    /**
     * Kenar renklerinden türetilen, kurumsal tonla harmanlanmış yumuşak geçiş.
     */
    private function drawGradient(GdImage $canvas, GdImage $source, int $width, int $height, bool $horizontal): void
    {
        $edges = $this->edgeColors($source);
        $brand = $this->hexToRgb(BrandPalette::DARK);

        $from = $horizontal ? $edges['left'] : $edges['top'];
        $to = $horizontal ? $edges['right'] : $edges['bottom'];
        $steps = max(1, ($horizontal ? $width : $height) - 1);

        for ($i = 0; $i <= $steps; $i++) {
            $ratio = $i / $steps;
            $color = $this->mixColors($from, $to, $ratio);
            $color = $this->mixColors($color, $brand, 0.18);
            $color = $this->adjustBrightness($color, -10);

            $allocated = imagecolorallocate($canvas, $color[0], $color[1], $color[2]);

            if ($horizontal) {
                imagefilledrectangle($canvas, $i, 0, $i, $height - 1, $allocated);
            } else {
                imagefilledrectangle($canvas, 0, $i, $width - 1, $i, $allocated);
            }
        }
    }

    /**
     * Boşluk kalan yönde görselin kenar şeridini aynalayarak uzatır.
     */
    private function drawMirroredBands(
        GdImage $canvas,
        GdImage $source,
        int $width,
        int $height,
        int $drawX,
        int $drawY,
        int $drawWidth,
        int $drawHeight
    ): void {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        if ($drawY > 0 && $drawWidth > 0) {
            $verticalScale = $drawHeight / $sourceHeight;

            // Üst boşluk: görselin üst şeridi dikeyde aynalanıp yukarı doğru uzatılır.
            $stripHeight = max(1, min($sourceHeight, (int) round($drawY / max($verticalScale, 0.0001))));
            $this->copyMirroredStrip($canvas, $source, 0, 0, $sourceWidth, $stripHeight, $drawX, 0, $drawWidth, $drawY, IMG_FLIP_VERTICAL);

            $bottomGap = $height - ($drawY + $drawHeight);

            if ($bottomGap > 0) {
                $stripHeight = max(1, min($sourceHeight, (int) round($bottomGap / max($verticalScale, 0.0001))));
                $this->copyMirroredStrip(
                    $canvas,
                    $source,
                    0,
                    $sourceHeight - $stripHeight,
                    $sourceWidth,
                    $stripHeight,
                    $drawX,
                    $drawY + $drawHeight,
                    $drawWidth,
                    $bottomGap,
                    IMG_FLIP_VERTICAL
                );
            }
        }

        if ($drawX > 0 && $drawHeight > 0) {
            $horizontalScale = $drawWidth / $sourceWidth;

            $stripWidth = max(1, min($sourceWidth, (int) round($drawX / max($horizontalScale, 0.0001))));
            $this->copyMirroredStrip($canvas, $source, 0, 0, $stripWidth, $sourceHeight, 0, $drawY, $drawX, $drawHeight, IMG_FLIP_HORIZONTAL);

            $rightGap = $width - ($drawX + $drawWidth);

            if ($rightGap > 0) {
                $stripWidth = max(1, min($sourceWidth, (int) round($rightGap / max($horizontalScale, 0.0001))));
                $this->copyMirroredStrip(
                    $canvas,
                    $source,
                    $sourceWidth - $stripWidth,
                    0,
                    $stripWidth,
                    $sourceHeight,
                    $drawX + $drawWidth,
                    $drawY,
                    $rightGap,
                    $drawHeight,
                    IMG_FLIP_HORIZONTAL
                );
            }
        }
    }

    private function copyMirroredStrip(
        GdImage $canvas,
        GdImage $source,
        int $sourceX,
        int $sourceY,
        int $sourceWidth,
        int $sourceHeight,
        int $targetX,
        int $targetY,
        int $targetWidth,
        int $targetHeight,
        int $flipMode
    ): void {
        if ($targetWidth < 1 || $targetHeight < 1) {
            return;
        }

        $strip = imagecreatetruecolor($sourceWidth, $sourceHeight);
        imagecopy($strip, $source, 0, 0, $sourceX, $sourceY, $sourceWidth, $sourceHeight);
        imageflip($strip, $flipMode);

        // Hafif bulanıklık dikiş çizgisini gizler.
        imagefilter($strip, IMG_FILTER_GAUSSIAN_BLUR);

        imagecopyresampled($canvas, $strip, $targetX, $targetY, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);
        imagedestroy($strip);
    }

    /**
     * Net görselin kenarına doğru sönümlenen yumuşak gölge; dolgu ile görsel
     * arasındaki geçişi keskin bir çizgi yerine doğal bir derinliğe çevirir.
     */
    private function softenSeams(
        GdImage $canvas,
        int $width,
        int $height,
        int $drawX,
        int $drawY,
        int $drawWidth,
        int $drawHeight
    ): void {
        $depth = 22;

        for ($i = 0; $i < $depth; $i++) {
            $strength = (int) round(30 * (1 - $i / $depth) ** 1.6);

            if ($strength <= 0) {
                continue;
            }

            $color = imagecolorallocatealpha($canvas, 0, 0, 0, 127 - $strength);

            if ($drawY > 0) {
                $top = $drawY - 1 - $i;
                $bottom = $drawY + $drawHeight + $i;

                if ($top >= 0) {
                    imagefilledrectangle($canvas, 0, $top, $width - 1, $top, $color);
                }

                if ($bottom <= $height - 1) {
                    imagefilledrectangle($canvas, 0, $bottom, $width - 1, $bottom, $color);
                }
            }

            if ($drawX > 0) {
                $left = $drawX - 1 - $i;
                $right = $drawX + $drawWidth + $i;

                if ($left >= 0) {
                    imagefilledrectangle($canvas, $left, 0, $left, $height - 1, $color);
                }

                if ($right <= $width - 1) {
                    imagefilledrectangle($canvas, $right, 0, $right, $height - 1, $color);
                }
            }
        }
    }

    /**
     * Görselin dört kenarındaki ortalama renkler.
     *
     * @return array{top: array{int, int, int}, bottom: array{int, int, int}, left: array{int, int, int}, right: array{int, int, int}}
     */
    private function edgeColors(GdImage $source): array
    {
        $size = 32;
        $thumb = imagecreatetruecolor($size, $size);
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $size, $size, imagesx($source), imagesy($source));

        $band = 4;
        $sums = [
            'top' => [0, 0, 0, 0],
            'bottom' => [0, 0, 0, 0],
            'left' => [0, 0, 0, 0],
            'right' => [0, 0, 0, 0],
        ];

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $rgb = imagecolorat($thumb, $x, $y);
                $pixel = [($rgb >> 16) & 0xFF, ($rgb >> 8) & 0xFF, $rgb & 0xFF];

                $regions = [];

                if ($y < $band) {
                    $regions[] = 'top';
                }

                if ($y >= $size - $band) {
                    $regions[] = 'bottom';
                }

                if ($x < $band) {
                    $regions[] = 'left';
                }

                if ($x >= $size - $band) {
                    $regions[] = 'right';
                }

                foreach ($regions as $region) {
                    $sums[$region][0] += $pixel[0];
                    $sums[$region][1] += $pixel[1];
                    $sums[$region][2] += $pixel[2];
                    $sums[$region][3]++;
                }
            }
        }

        imagedestroy($thumb);

        $average = function (array $sum): array {
            $count = max(1, $sum[3]);

            return [
                (int) round($sum[0] / $count),
                (int) round($sum[1] / $count),
                (int) round($sum[2] / $count),
            ];
        };

        return [
            'top' => $average($sums['top']),
            'bottom' => $average($sums['bottom']),
            'left' => $average($sums['left']),
            'right' => $average($sums['right']),
        ];
    }

    /**
     * @param  array{int, int, int}  $from
     * @param  array{int, int, int}  $to
     * @return array{int, int, int}
     */
    private function mixColors(array $from, array $to, float $ratio): array
    {
        $ratio = max(0.0, min(1.0, $ratio));

        return [
            (int) round($from[0] + ($to[0] - $from[0]) * $ratio),
            (int) round($from[1] + ($to[1] - $from[1]) * $ratio),
            (int) round($from[2] + ($to[2] - $from[2]) * $ratio),
        ];
    }

    /**
     * @param  array{int, int, int}  $color
     * @return array{int, int, int}
     */
    private function adjustBrightness(array $color, int $amount): array
    {
        return [
            max(0, min(255, $color[0] + $amount)),
            max(0, min(255, $color[1] + $amount)),
            max(0, min(255, $color[2] + $amount)),
        ];
    }

    /**
     * @return array{int, int, int}
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    private function store(GdImage $canvas, string $relativePath, string $format): void
    {
        $disk = Storage::disk('public');
        $disk->makeDirectory(dirname($relativePath));

        $absolute = $disk->path($relativePath);

        if ($format === 'webp' && function_exists('imagewebp')) {
            $saved = imagewebp($canvas, $absolute, 82);
        } else {
            imageinterlace($canvas, true);
            $saved = imagejpeg($canvas, $absolute, 86);
        }

        if (! $saved) {
            throw new RuntimeException("Görsel yazılamadı: {$relativePath}");
        }
    }

    private function ratioLabel(int $width, int $height): string
    {
        $divisor = $this->greatestCommonDivisor($width, $height);

        return sprintf('%d:%d', $width / $divisor, $height / $divisor);
    }

    private function greatestCommonDivisor(int $a, int $b): int
    {
        while ($b !== 0) {
            [$a, $b] = [$b, $a % $b];
        }

        return max(1, $a);
    }
}
