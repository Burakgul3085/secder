<?php

namespace App\Filament\Resources\ActivitySectionSettings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ActivitySectionSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('badge_text')->label('Rozet metni')->default('SECDER'),
            TextInput::make('title')->label('Başlık')->required()->default('Faaliyetlerimiz'),
            Textarea::make('description')
                ->label('Kısa açıklama')
                ->rows(3)
                ->default('Gaziantep\'te cami merkezli ilmi eğitim, sosyal projeler ve dayanışma çalışmalarımızla nesillerin yanında yer alıyoruz.')
                ->helperText('Türkçe metin admin’den düzenlenir. EN / AR / RU çevirileri dil dosyalarından gelir.')
                ->columnSpanFull(),
            Toggle::make('is_active')->default(true)->label('Aktif'),
        ])->columns(2);
    }
}
