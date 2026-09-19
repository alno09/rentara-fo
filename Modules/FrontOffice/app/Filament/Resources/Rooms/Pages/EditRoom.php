<?php

namespace Modules\FrontOffice\Filament\Resources\Rooms\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\FrontOffice\Filament\Resources\Rooms\RoomResource;

class EditRoom extends EditRecord
{
    protected static string $resource = RoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}