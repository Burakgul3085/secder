<?php

namespace App\Filament\Resources\BankAccounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BankAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table->defaultSort('sort_order')
            ->columns([
                TextColumn::make('recipient_name')->label('Hesap Adı')->searchable(),
                TextColumn::make('bank_name')->label('Banka')->searchable(),
                TextColumn::make('branch_name')->label('Şube')->placeholder('-'),
                TextColumn::make('iban')->label('IBAN')->copyable(),
                TextColumn::make('account_number')->label('Hesap No')->placeholder('-'),
                TextColumn::make('currency')->badge()->label('Döviz'),
                IconColumn::make('is_active')->boolean()->label('Aktif'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
