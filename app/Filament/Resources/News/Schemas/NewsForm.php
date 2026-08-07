<?php

namespace App\Filament\Resources\News\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class NewsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('i18n_tabs')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('TR')
                        ->schema([
                            Grid::make(1)->schema([
                                TextInput::make('title_i18n.tr')->label('Başlık (TR)')->required(),
                                Textarea::make('summary_i18n.tr')
                                    ->label('Kısa özet (TR)')
                                    ->rows(3)
                                    ->helperText('Ana sayfada kart içinde kısa açıklama olarak görünür.'),
                                RichEditor::make('content_i18n.tr')->label('İçerik (TR)')->required(),
                            ]),
                        ]),
                    Tab::make('EN')
                        ->schema([
                            Grid::make(1)->schema([
                                TextInput::make('title_i18n.en')->label('Title (EN)'),
                                Textarea::make('summary_i18n.en')
                                    ->label('Short summary (EN)')
                                    ->rows(3),
                                RichEditor::make('content_i18n.en')->label('Content (EN)'),
                            ]),
                        ]),
                    Tab::make('AR')
                        ->schema([
                            Grid::make(1)->schema([
                                TextInput::make('title_i18n.ar')->label('العنوان (AR)'),
                                Textarea::make('summary_i18n.ar')
                                    ->label('الملخص القصير (AR)')
                                    ->rows(3),
                                RichEditor::make('content_i18n.ar')->label('المحتوى (AR)'),
                            ]),
                        ]),
                ]),
            FileUpload::make('cover_image')
                ->label('Haber kapak görseli')
                ->disk('public')
                ->directory('news')
                ->image()
                ->imageEditor()
                ->helperText('Öneri: 1200x700 px, JPG/PNG/WebP')
                ->columnSpanFull(),
            FileUpload::make('gallery_images')
                ->label('Ekstra görseller (galeri)')
                ->disk('public')
                ->directory('news/gallery')
                ->image()
                ->multiple()
                ->reorderable()
                ->appendFiles()
                ->helperText('Detay sayfasında galeri olarak görünür.')
                ->columnSpanFull(),
            FileUpload::make('gallery_videos')
                ->label('Galeri videoları')
                ->disk('public')
                ->directory('news/gallery-videos')
                ->multiple()
                ->reorderable()
                ->appendFiles()
                ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska'])
                ->helperText('Detay sayfasında video galerisi olarak görünür. Boyut sınırı yoktur.')
                ->columnSpanFull(),
            DateTimePicker::make('published_at')->label('Yayın Tarihi')->seconds(false),
            Toggle::make('is_active')->default(true)->label('Aktif'),
        ])->columns(2);
    }
}
