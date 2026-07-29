<?php

namespace App\Filament\Resources\HeroSlides\Schemas;

use App\Models\HeroSlide;
use App\Support\HeroImageRenderer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Storage;

class HeroSlideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Hidden::make('headline')
                ->default('Hero Slayt')
                ->dehydrated(true),

            Section::make('Slayt görseli')
                ->description('Tek görsel yükleyin; sistem masaüstü, tablet ve telefon bandları için ayrı dosyaları otomatik üretir. Görseliniz kırpılmaz, oranı bozulmaz.')
                ->schema([
                    FileUpload::make('image_path')
                        ->disk('public')
                        ->directory('hero')
                        ->image()
                        ->imageEditor()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->rules(['required', 'file', 'max:20480'])
                        ->required()
                        ->label('Görsel')
                        ->helperText(static::uploadHelpText()),

                    Select::make('fit_mode')
                        ->label('Görsel yerleşimi')
                        ->options(HeroImageRenderer::FIT_MODES)
                        ->default('smart')
                        ->native(false)
                        ->helperText(
                            'Masaüstü bandı çok geniş olduğu için «akıllı» seçimde görsel alanı tamamen doldurur '
                            . '(gerekirse üst/alt kenardan bir miktar kırpılır). Tablet ve telefonda kompozisyon '
                            . 'bozulmasın diye görselin tamamı gösterilir, artan alan zemin dolgusuyla tamamlanır. '
                            . 'Kırpma görselin %45\'ini aşacaksa sistem otomatik olarak tamamını gösterir.'
                        ),

                    Select::make('fill_mode')
                        ->label('Zemin dolgusu')
                        ->options(HeroImageRenderer::FILL_MODES)
                        ->default('auto')
                        ->native(false)
                        ->helperText('Görsel bandı tam doldurmadığında artan alan bu yöntemle tamamlanır. Otomatik seçim, görseli analiz ederek en uygun yöntemi kullanır.'),

                    Html::make(fn (?HeroSlide $record): HtmlString => static::previewHtml($record)),
                ]),

            Section::make('Cihaza özel görseller (isteğe bağlı)')
                ->description('Yalnızca telefon veya tablette farklı bir kompozisyon göstermek istiyorsanız doldurun. Bunlar da otomatik olarak ilgili banda oturtulur.')
                ->collapsed()
                ->schema([
                    FileUpload::make('image_path_tablet')
                        ->disk('public')
                        ->directory('hero')
                        ->image()
                        ->imageEditor()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->rules(['nullable', 'file', 'max:20480'])
                        ->label('Tablet görseli')
                        ->helperText('Boş bırakılırsa ana görsel kullanılır. İdeal oran 2:1 (örn. 1536x768 px).'),

                    FileUpload::make('image_path_mobile')
                        ->disk('public')
                        ->directory('hero')
                        ->image()
                        ->imageEditor()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->rules(['nullable', 'file', 'max:20480'])
                        ->label('Telefon görseli')
                        ->helperText('Boş bırakılırsa tablet/ana görsel kullanılır. İdeal oran 1:1 (örn. 1080x1080 px).'),
                ]),

            Section::make('Yayın ayarları')
                ->schema([
                    TextInput::make('sort_order')->numeric()->default(0)->label('Sıralama'),
                    Toggle::make('is_active')->default(true)->label('Aktif'),
                ]),
        ]);
    }

    /**
     * Yükleme alanının altında gösterilen ideal ölçü rehberi.
     */
    private static function uploadHelpText(): HtmlString
    {
        $targets = collect(app(HeroImageRenderer::class)->targets())
            ->map(fn (array $target): string => sprintf(
                '%s %dx%d px (%s)',
                $target['label'],
                $target['width'],
                $target['height'],
                $target['ratio']
            ))
            ->implode(' · ');

        return new HtmlString(
            'Herhangi bir ölçüde yükleyebilirsiniz. En temiz sonuç için geniş/yatay bir görsel seçin; '
            . 'ideal ölçüler: <strong>' . e($targets) . '</strong>. Maksimum 20 MB, JPG/PNG/WebP.'
        );
    }

    /**
     * Kaydedilmiş slayt için üretilen varyantların önizlemesi ve uyarıları.
     */
    private static function previewHtml(?HeroSlide $record): HtmlString
    {
        if (! $record?->exists) {
            return new HtmlString(
                '<p class="text-sm text-gray-500 dark:text-gray-400">Slaytı kaydettikten sonra üretilen masaüstü, tablet ve telefon görselleri burada görünecek.</p>'
            );
        }

        $variants = [
            'Masaüstü (1920x480)' => $record->rendered_desktop_path,
            'Tablet (1536x768)' => $record->rendered_tablet_path,
            'Telefon (1080x1080)' => $record->rendered_mobile_path,
        ];

        $cards = '';

        foreach ($variants as $label => $path) {
            if (blank($path) || ! Storage::disk('public')->exists($path)) {
                continue;
            }

            $cards .= sprintf(
                '<figure class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5">
                    <img src="%s" alt="%s" class="block w-full" loading="lazy">
                    <figcaption class="px-3 py-2 text-xs font-medium text-gray-600 dark:text-gray-300">%s</figcaption>
                </figure>',
                e(Storage::url($path)),
                e($label),
                e($label)
            );
        }

        if ($cards === '') {
            return new HtmlString(
                '<p class="text-sm text-amber-600 dark:text-amber-400">Görseller henüz üretilmedi. Slaytı tekrar kaydedin veya listedeki «Görselleri yeniden üret» aksiyonunu kullanın.</p>'
            );
        }

        $warnings = '';

        foreach ($record->renderWarnings() as $warning) {
            $warnings .= '<li>' . e($warning) . '</li>';
        }

        $warningBlock = $warnings === ''
            ? ''
            : '<ul class="mt-3 list-disc space-y-1 pl-5 text-xs text-amber-600 dark:text-amber-400">' . $warnings . '</ul>';

        return new HtmlString(
            '<div class="space-y-3">'
            . '<p class="text-sm font-medium text-gray-700 dark:text-gray-200">Üretilen görseller</p>'
            . '<div class="grid gap-3 sm:grid-cols-3">' . $cards . '</div>'
            . $warningBlock
            . '</div>'
        );
    }
}
