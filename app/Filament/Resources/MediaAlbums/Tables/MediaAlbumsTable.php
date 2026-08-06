<?php

namespace App\Filament\Resources\MediaAlbums\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MediaAlbumsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('title')->label('Başlık')->searchable()->sortable(),
                TextColumn::make('slug')->label('Slug')->toggleable(),
                TextColumn::make('media_items_count')
                    ->counts('mediaItems')
                    ->label('Öğe')
                    ->sortable(),
                TextColumn::make('sort_order')->label('Sıra')->sortable(),
                IconColumn::make('is_active')->boolean()->label('Aktif'),
                TextColumn::make('updated_at')->dateTime('d.m.Y H:i')->label('Güncelleme')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
