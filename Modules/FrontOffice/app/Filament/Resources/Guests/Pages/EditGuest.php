<?php

namespace Modules\FrontOffice\Filament\Resources\Guests\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\FrontOffice\Filament\Resources\Guests\GuestResource;

class EditGuest extends EditRecord
{
    protected static string $resource = GuestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}