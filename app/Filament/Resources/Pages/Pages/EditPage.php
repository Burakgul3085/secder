<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Pages\BaseEditRecord;
use App\Filament\Resources\Pages\PageResource;
use App\Support\PageI18n;
use Filament\Actions\DeleteAction;

class EditPage extends BaseEditRecord
{
    protected static string $resource = PageResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return PageI18n::hydrateForm($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return PageI18n::prepareForSave($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
