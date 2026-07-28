<?php

namespace App\Filament\Resources\News\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NewsTable
{
    public static function configure(Table $table): Table
    {
        return $table->defaultSort('published_at', 'desc')
            ->columns([
                ImageColumn::make('cover_image')->label('Kapak')->disk('public')->square(),
                TextColumn::make('title')->searchable()->label('Başlık'),
                TextColumn::make('summary')->label('Kısa Özet')->limit(60)->toggleable(),
                TextColumn::make('published_at')->dateTime('d.m.Y H:i')->label('Yayın Tarihi'),
                IconColumn::make('is_active')->boolean()->label('Aktif'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
