<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Ad Soyad')->required(),
                TextInput::make('email')->label('E-posta')->email()->required(),
                TextInput::make('password')
                    ->label('Sifre')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn ($state): bool => filled($state)),
                Select::make('role')
                    ->label('Rol')
                    ->options([
                        'super_admin' => 'Super Admin',
                        'editor' => 'Editor',
                        'viewer' => 'Goruntuleyici',
                    ])
                    ->default('editor')
                    ->required(),
                Toggle::make('is_active')->label('Aktif')->default(true),
            ]);
    }
}
