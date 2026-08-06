<?php

namespace App\Filament\Resources\MediaAlbums\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class MediaAlbumForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Başlık')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                    if (filled($get('slug'))) {
                        return;
                    }
                    $set('slug', Str::slug((string) $state));
                })
                ->helperText('Örn: Cami Resimleri, Medrese Resimleri'),
            TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->unique(ignoreRecord: true),
            Textarea::make('description')
                ->label('Açıklama')
                ->rows(3)
                ->columnSpanFull(),
            FileUpload::make('gallery_images')
                ->label('Fotoğraflar')
                ->disk('public')
                ->directory('media/albums/images')
                ->multiple()
                ->reorderable()
                ->image()
                ->imageEditor()
                ->maxSize(204800)
                ->helperText('Bu albüme ait fotoğraflar. İstediğiniz zaman Medya Öğeleri menüsünden başka başlığa taşıyabilirsiniz.'),
            FileUpload::make('gallery_videos')
                ->label('Videolar')
                ->disk('public')
                ->directory('media/albums/videos')
                ->multiple()
                ->reorderable()
                ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/quicktime', 'video/x-matroska', 'video/x-msvideo'])
                ->maxSize(2097152)
                ->helperText('MP4/WEBM/MOV/MKV — en fazla 2 GB.'),
            TextInput::make('sort_order')
                ->label('Sıralama')
                ->numeric()
                ->default(0),
            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ])->columns(2);
    }
}
