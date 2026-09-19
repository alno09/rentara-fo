<?php

namespace Modules\FrontOffice\Filament\Resources\RoomTypes\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\FrontOffice\Filament\Resources\RoomTypes\RoomTypeResource;

class EditRoomType extends EditRecord
{
    protected static string $resource = RoomTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}