<?php

namespace Modules\FrontOffice\Filament\Resources\Guests\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\FrontOffice\Filament\Resources\Guests\GuestResource;

class ListGuests extends ListRecords
{
    protected static string $resource = GuestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}