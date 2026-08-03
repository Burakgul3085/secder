<?php

namespace App\Filament\Resources\HeroSlides\Schemas;

use App\Models\HeroSlide;
use App\Support\HeroImageSpec;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class HeroSlideForm
{
    public static function configure(Schema $schema): Schema
    {
        $desktop = HeroImageSpec::DEVICES['desktop'];
        $tablet = HeroImageSpec::DEVICES['tablet'];
        $mobile = HeroImageSpec::DEVICES['mobile'];

        return $schema->components([
            Hidden::make('headline')
                ->default('Hero Slayt')
                ->dehydrated(true),

            Hidden::make('fit_mode')
                ->default('cover')
                ->dehydrated(true),

            Hidden::make('fill_mode')
                ->default('auto')
                ->dehydrated(true),

            Section::make('Hero görselleri (cihaz bazlı)')
                ->description('Her cihaz için ayrı kompozisyon yükleyin. Aşağıdaki ölçülere birebir uyarsanız sitede kırpma, taşma veya boş kenar olmaz.')
                ->schema([
                    Html::make(fn (): HtmlString => static::sizeGuideHtml()),

                    FileUpload::make('image_path')
                        ->disk('public')
                        ->directory('hero')
                        ->image()
                        ->imageEditor()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->rules(['required', 'file', 'max:20480'])
                        ->required()
                        ->label(sprintf('Masaüstü · %s', HeroImageSpec::sizeLabel('desktop')))
                        ->helperText(sprintf(
                            'Zorunlu · Tam olarak %d×%d px yükleyin. %s',
                            $desktop['width'],
                            $desktop['height'],
                            $desktop['hint']
                        )),

                    FileUpload::make('image_path_tablet')
                        ->disk('public')
                        ->directory('hero')
                        ->image()
                        ->imageEditor()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->rules(['required', 'file', 'max:20480'])
                        ->required()
                        ->label(sprintf('Tablet · %s', HeroImageSpec::sizeLabel('tablet')))
                        ->helperText(sprintf(
                            'Zorunlu · Tam olarak %d×%d px yükleyin. %s',
                            $tablet['width'],
                            $tablet['height'],
                            $tablet['hint']
                        )),

                    FileUpload::make('image_path_mobile')
                        ->disk('public')
                        ->directory('hero')
                        ->image()
                        ->imageEditor()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->rules(['required', 'file', 'max:20480'])
                        ->required()
                        ->label(sprintf('Telefon · %s', HeroImageSpec::sizeLabel('mobile')))
                        ->helperText(sprintf(
                            'Zorunlu · Tam olarak %d×%d px yükleyin. %s',
                            $mobile['width'],
                            $mobile['height'],
                            $mobile['hint']
                        )),

                    Html::make(fn (?HeroSlide $record): HtmlString => static::previewHtml($record)),
                ]),

            Section::make('Yayın ayarları')
                ->schema([
                    TextInput::make('sort_order')->numeric()->default(0)->label('Sıralama'),
                    Toggle::make('is_active')->default(true)->label('Aktif'),
                ]),
        ]);
    }

    private static function sizeGuideHtml(): HtmlString
    {
        $rows = '';

        foreach (HeroImageSpec::DEVICES as $device) {
            $rows .= sprintf(
                '<tr class="border-t border-cyan-200/80 dark:border-cyan-800">
                    <td class="py-2 pr-3 font-semibold text-slate-900 dark:text-white">%s</td>
                    <td class="py-2 pr-3 font-mono text-cyan-800 dark:text-cyan-200">%d×%d px</td>
                    <td class="py-2 pr-3">%s</td>
                    <td class="py-2 text-slate-600 dark:text-slate-300">%s</td>
                </tr>',
                e($device['label']),
                $device['width'],
                $device['height'],
                e($device['ratio']),
                e($device['breakpoint'])
            );
        }

        return new HtmlString(
            <<<HTML
            <div class="rounded-xl border border-cyan-200 bg-cyan-50/80 p-4 text-sm text-slate-700 dark:border-cyan-900 dark:bg-cyan-950/40 dark:text-slate-200">
                <p class="text-base font-semibold text-slate-900 dark:text-white">Yükleme ölçüleri (zorunlu)</p>
                <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">
                    Gemini / Midjourney / Canva’da aşağıdaki piksel değerlerini birebir yazın. Site çerçevesi aynı oranda açılır; ölçü doğruysa kırpma veya boşluk oluşmaz.
                </p>
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full min-w-[420px] text-left text-xs sm:text-sm">
                        <thead>
                            <tr class="text-slate-500 dark:text-slate-400">
                                <th class="pb-2 pr-3 font-medium">Cihaz</th>
                                <th class="pb-2 pr-3 font-medium">Ölçü</th>
                                <th class="pb-2 pr-3 font-medium">Oran</th>
                                <th class="pb-2 font-medium">Ekran</th>
                            </tr>
                        </thead>
                        <tbody>{$rows}</tbody>
                    </table>
                </div>
                <div class="mt-3 rounded-lg border border-cyan-300/70 bg-white/70 p-3 text-xs dark:border-cyan-800 dark:bg-cyan-950/50">
                    <p class="font-semibold text-slate-900 dark:text-white">Masaüstü hatırlatma (kısa banner)</p>
                    <p class="mt-1 text-slate-600 dark:text-slate-300">
                        Masaüstü <strong>1920×480 px (4:1)</strong> — Aile ve Nesil gibi alçak yatay afiş.
                        Gemini’de örnek: <span class="font-mono text-cyan-800 dark:text-cyan-200">Create a 1920x480 px wide banner image…</span>
                    </p>
                    <p class="mt-2 text-slate-600 dark:text-slate-300">
                        Tablet <strong>1536×1024</strong> · Telefon <strong>1080×1350</strong> (bunlar değişmedi).
                    </p>
                </div>
                <ul class="mt-3 list-disc space-y-1 pl-5 text-xs text-slate-600 dark:text-slate-300">
                    <li>JPG / PNG / WebP · en fazla 20 MB</li>
                    <li>Önemli yazı ve logo kenardan en az 60–80 px içeride olsun</li>
                    <li>Telefon görseli dikey olsun; masaüstü afişini telefona sıkıştırmayın</li>
                </ul>
            </div>
            HTML
        );
    }

    private static function previewHtml(?HeroSlide $record): HtmlString
    {
        if (! $record?->exists) {
            return new HtmlString(
                '<p class="text-sm text-gray-500 dark:text-gray-400">Üç görseli yükleyip kaydettikten sonra masaüstü, tablet ve telefon önizlemeleri burada görünür.</p>'
            );
        }

        $variants = [
            'desktop' => $record->rendered_desktop_path,
            'tablet' => $record->rendered_tablet_path,
            'mobile' => $record->rendered_mobile_path,
        ];

        $cards = '';

        foreach ($variants as $device => $path) {
            $label = HeroImageSpec::label($device) . ' · ' . HeroImageSpec::sizeLabel($device);

            if (blank($path) || ! Storage::disk('public')->exists($path)) {
                $cards .= sprintf(
                    '<figure class="flex min-h-[120px] items-center justify-center overflow-hidden rounded-xl border border-dashed border-amber-300 bg-amber-50/60 px-3 py-6 text-center text-xs font-medium text-amber-800 dark:border-amber-700 dark:bg-amber-950/30 dark:text-amber-200">
                        %s<br><span class="mt-1 block font-normal opacity-80">Henüz üretilmedi</span>
                    </figure>',
                    e($label)
                );

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

        $warnings = '';

        foreach ($record->renderWarnings() as $warning) {
            $warnings .= '<li>' . e($warning) . '</li>';
        }

        $warningBlock = $warnings === ''
            ? '<p class="text-xs text-emerald-700 dark:text-emerald-400">Üç cihaz bandı hazır. Ölçüler doğruysa ön yüzde kırpma veya boşluk olmamalı.</p>'
            : '<ul class="mt-1 list-disc space-y-1 pl-5 text-xs text-amber-600 dark:text-amber-400">' . $warnings . '</ul>';

        return new HtmlString(
            '<div class="space-y-3">'
            . '<p class="text-sm font-medium text-gray-700 dark:text-gray-200">Cihaz önizlemeleri</p>'
            . '<div class="grid gap-3 sm:grid-cols-3">' . $cards . '</div>'
            . $warningBlock
            . '</div>'
        );
    }
}
