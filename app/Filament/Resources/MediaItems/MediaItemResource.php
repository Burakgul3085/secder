<?php

namespace App\Filament\Resources\MediaItems;

use App\Filament\Resources\MediaItems\Pages\ListMediaItems;
use App\Filament\Resources\MediaItems\Tables\MediaItemsTable;
use App\Models\MediaItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MediaItemResource extends Resource
{
    protected static ?string $model = MediaItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $navigationLabel = 'Medya Öğeleri / Taşı';

    protected static string|\UnitEnum|null $navigationGroup = 'İçerik Yönetimi';

    protected static ?string $modelLabel = 'Medya Öğesi';

    protected static ?string $pluralModelLabel = 'Medya Öğeleri';

    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check();
    }

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return auth()->check() && auth()->user()?->canManageContent();
    }

    public static function canDelete($record): bool
    {
        return auth()->check() && auth()->user()?->canManageContent();
    }

    public static function table(Table $table): Table
    {
        return MediaItemsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['project', 'album']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMediaItems::route('/'),
        ];
    }
}
