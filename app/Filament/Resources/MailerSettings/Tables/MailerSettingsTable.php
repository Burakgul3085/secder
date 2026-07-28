<?php

namespace App\Filament\Resources\MailerSettings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MailerSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->recordUrl(null)
            ->columns([
                TextColumn::make('mailer_from_name')->label('Gönderici Adı')->searchable(),
                TextColumn::make('mailer_from_address')->label('Gönderici E-posta')->searchable()->copyable(),
                TextColumn::make('mailer_notification_email')->label('Bildirim E-posta')->searchable()->copyable(),
                TextColumn::make('mailer_host')->label('SMTP Host')->searchable(),
                TextColumn::make('mailer_port')->label('Port'),
                TextColumn::make('mailer_encryption')->label('Şifreleme')->badge(),
                TextColumn::make('updated_at')->label('Güncelleme')->dateTime('d.m.Y H:i'),
            ])
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
