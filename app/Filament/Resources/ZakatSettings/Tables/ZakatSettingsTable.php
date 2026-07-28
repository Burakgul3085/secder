<?php

namespace App\Filament\Resources\ZakatSettings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ZakatSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nisap_grams')->label('Nisap (gr)'),
                TextColumn::make('rate')->label('Oran'),
                IconColumn::make('is_active')->boolean()->label('Aktif'),
                TextColumn::make('updated_at')->label('Güncelleme')->dateTime('d.m.Y H:i'),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                EditAction::make()->label('Düzenle'),
                DeleteAction::make()->label('Sil'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Seçilenleri sil'),
                ]),
            ]);
    }
}
