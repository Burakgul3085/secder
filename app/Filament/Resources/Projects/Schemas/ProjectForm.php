<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        $locales = [
            'tr' => 'Turkce',
            'en' => 'English',
            'ar' => 'Arabic',
        ];

        $translationTabs = [];
        foreach ($locales as $locale => $label) {
            $translationTabs[] = Tabs\Tab::make($label)
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make("title_i18n.$locale")
                            ->label('Baslik')
                            ->required($locale === 'tr')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) use ($locale): void {
                                if ($locale !== 'tr' || filled($get('slug'))) {
                                    return;
                                }
                                $set('slug', Str::slug((string) $state));
                            }),
                        Textarea::make("description_i18n.$locale")
                            ->label('Kisa aciklama')
                            ->rows(3),
                    ]),
                    Textarea::make("content_i18n.$locale")
                        ->label('Detay icerik')
                        ->rows(8)
                        ->columnSpanFull(),
                    Grid::make(2)->schema([
                        TextInput::make("detail_item_1_title_i18n.$locale")
                            ->label('Detay acilir menu 1 baslik')
                            ->default('Hizli Mudahale'),
                        Textarea::make("detail_item_1_text_i18n.$locale")
                            ->label('Detay acilir menu 1 metin')
                            ->rows(2)
                            ->default('Kriz anlarinda hizli mudahale ederek ihtiyac sahiplerine destek sagliyoruz.'),
                        TextInput::make("detail_item_2_title_i18n.$locale")
                            ->label('Detay acilir menu 2 baslik')
                            ->default('Uzun Vadeli Cozumler'),
                        Textarea::make("detail_item_2_text_i18n.$locale")
                            ->label('Detay acilir menu 2 metin')
                            ->rows(2)
                            ->default('Surdurulebilir etki icin yerel isbirligi modelleri gelistiriyoruz.'),
                        TextInput::make("detail_item_3_title_i18n.$locale")
                            ->label('Detay acilir menu 3 baslik')
                            ->default('Toplum Destegi'),
                        Textarea::make("detail_item_3_text_i18n.$locale")
                            ->label('Detay acilir menu 3 metin')
                            ->rows(2)
                            ->default('Toplum odakli faaliyetlerle kalici fayda uretmeyi hedefliyoruz.'),
                    ]),
                ]);
        }

        return $schema->components([
            Tabs::make('translations')
                ->label('Dil bazli icerik')
                ->persistTabInQueryString()
                ->tabs($translationTabs)
                ->columnSpanFull(),
            TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->unique(ignoreRecord: true),
            TextInput::make('donation_amount')
                ->label('Bagis Tutari')
                ->numeric()
                ->minValue(0)
                ->prefix('₺')
                ->placeholder('Orn: 1500'),
            TextInput::make('donation_currency')
                ->label('Para Birimi')
                ->default('TL')
                ->maxLength(10),
            FileUpload::make('cover_image')
                ->disk('public')
                ->directory('projects')
                ->image()
                ->label('Kapak Gorseli'),
            FileUpload::make('gallery_images')
                ->label('Faaliyet Galeri Fotoğrafları')
                ->disk('public')
                ->directory('projects/gallery-images')
                ->multiple()
                ->reorderable()
                ->image()
                ->imageEditor()
                ->maxSize(204800)
                ->helperText('Birden fazla fotoğraf ekleyebilirsiniz. Medya menüsünden başka başlıklara taşıyabilirsiniz.'),
            FileUpload::make('gallery_videos')
                ->label('Faaliyet Galeri Videolari')
                ->disk('public')
                ->directory('projects/gallery-videos')
                ->multiple()
                ->reorderable()
                ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/quicktime', 'video/x-matroska', 'video/x-msvideo'])
                ->maxSize(2097152)
                ->helperText('MP4/WEBM/MOV/MKV — en fazla 2 GB. Büyük dosyalarda yükleme birkaç dakika sürebilir.'),
            Select::make('status')->label('Durum')->options([
                'devam-ediyor' => 'Devam Ediyor',
                'tamamlandi' => 'Tamamlandı',
            ])->required(),
            TextInput::make('sort_order')->numeric()->default(0)->label('Sıralama'),
            Toggle::make('is_active')->default(true)->label('Aktif'),
        ])->columns(2);
    }
}
