<?php

namespace App\Filament\Resources\ZakatSettings\Pages;

use App\Filament\Resources\ZakatSettings\ZakatSettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditZakatSetting extends EditRecord
{
    protected static string $resource = ZakatSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Sil'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['intro_tr'] = $data['intro_i18n']['tr'] ?? null;
        $data['legal_tr'] = $data['legal_text_i18n']['tr'] ?? null;
        $data['faq_tr'] = $data['faq_i18n']['tr'] ?? [];

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['intro_i18n'] = ['tr' => $data['intro_tr'] ?? null];
        $data['legal_text_i18n'] = ['tr' => $data['legal_tr'] ?? null];
        $data['faq_i18n'] = ['tr' => $data['faq_tr'] ?? []];

        unset($data['intro_tr'], $data['legal_tr'], $data['faq_tr']);

        return $data;
    }
}
