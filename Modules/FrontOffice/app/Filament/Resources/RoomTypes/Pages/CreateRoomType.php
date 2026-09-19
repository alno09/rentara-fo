<?php

namespace Modules\FrontOffice\Filament\Resources\RoomTypes\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\FrontOffice\Filament\Resources\RoomTypes\RoomTypeResource;

class CreateRoomType extends CreateRecord
{
    protected static string $resource = RoomTypeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl('index');
    }
}
