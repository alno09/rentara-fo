<?php

namespace Modules\FrontOffice\Filament\Resources\RoomTypes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\FrontOffice\Filament\Resources\RoomTypes\RoomTypeResource;

class ListRoomTypes extends ListRecords
{
    protected static string $resource = RoomTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}