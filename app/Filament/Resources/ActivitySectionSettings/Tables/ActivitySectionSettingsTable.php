<?php

namespace App\Filament\Resources\ActivitySectionSettings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivitySectionSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Başlık'),
                TextColumn::make('badge_text')->label('Rozet')->default('—'),
                IconColumn::make('is_active')->boolean()->label('Aktif'),
                TextColumn::make('updated_at')->dateTime('d.m.Y H:i')->label('Güncellendi'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
