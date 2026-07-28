<?php

namespace App\Filament\Resources\MailerSettings\Pages;

use App\Filament\Pages\BaseEditRecord;
use App\Filament\Resources\MailerSettings\MailerSettingResource;
use Filament\Actions\DeleteAction;

class EditMailerSetting extends BaseEditRecord
{
    protected static string $resource = MailerSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Sil'),
        ];
    }
}
