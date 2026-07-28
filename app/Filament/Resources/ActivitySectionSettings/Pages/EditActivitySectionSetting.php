<?php

namespace App\Filament\Resources\ActivitySectionSettings\Pages;

use App\Filament\Resources\ActivitySectionSettings\ActivitySectionSettingResource;
use App\Filament\Pages\BaseEditRecord;
use Filament\Actions\DeleteAction;

class EditActivitySectionSetting extends BaseEditRecord
{
    protected static string $resource = ActivitySectionSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Sil'),
        ];
    }
}
