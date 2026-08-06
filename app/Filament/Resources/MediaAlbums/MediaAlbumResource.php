<?php

namespace App\Filament\Resources\MediaAlbums;

use App\Filament\Resources\MediaAlbums\Pages\CreateMediaAlbum;
use App\Filament\Resources\MediaAlbums\Pages\EditMediaAlbum;
use App\Filament\Resources\MediaAlbums\Pages\ListMediaAlbums;
use App\Filament\Resources\MediaAlbums\Schemas\MediaAlbumForm;
use App\Filament\Resources\MediaAlbums\Tables\MediaAlbumsTable;
use App\Models\MediaAlbum;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MediaAlbumResource extends Resource
{
    protected static ?string $model = MediaAlbum::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Medya';

    protected static string|\UnitEnum|null $navigationGroup = 'İçerik Yönetimi';

    protected static ?string $modelLabel = 'Medya Albümü';

    protected static ?string $pluralModelLabel = 'Medya Albümleri';

    protected static ?int $navigationSort = 3;

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
        return auth()->check() && auth()->user()?->canManageContent();
    }

    public static function canEdit($record): bool
    {
        return auth()->check() && auth()->user()?->canManageContent();
    }

    public static function canDelete($record): bool
    {
        return auth()->check() && auth()->user()?->canManageContent();
    }

    public static function form(Schema $schema): Schema
    {
        return MediaAlbumForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MediaAlbumsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMediaAlbums::route('/'),
            'create' => CreateMediaAlbum::route('/create'),
            'edit' => EditMediaAlbum::route('/{record}/edit'),
        ];
    }
}
