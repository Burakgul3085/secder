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

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['mailer_host'] = 'smtp.gmail.com';
        $data['mailer_port'] = 587;
        $data['mailer_encryption'] = 'tls';

        return $data;
    }
}
