<?php

namespace App\Filament\Resources\MailerSettings;

use App\Filament\Resources\MailerSettings\Pages\EditMailerSetting;
use App\Filament\Resources\MailerSettings\Pages\ListMailerSettings;
use App\Filament\Resources\MailerSettings\Schemas\MailerSettingForm;
use App\Filament\Resources\MailerSettings\Tables\MailerSettingsTable;
use App\Models\Setting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MailerSettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Mailer Ayarları';

    protected static string|\UnitEnum|null $navigationGroup = 'Sistem Ayarları';

    protected static ?string $modelLabel = 'Mailer Ayarı';

    protected static ?string $pluralModelLabel = 'Mailer Ayarları';

    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()?->canManageAppearance();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return auth()->check() && auth()->user()?->canManageAppearance();
    }

    public static function canDelete($record): bool
    {
        return auth()->check() && auth()->user()?->canManageAppearance();
    }

    public static function form(Schema $schema): Schema
    {
        return MailerSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MailerSettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMailerSettings::route('/'),
            'edit' => EditMailerSetting::route('/{record}/edit'),
        ];
    }
}
