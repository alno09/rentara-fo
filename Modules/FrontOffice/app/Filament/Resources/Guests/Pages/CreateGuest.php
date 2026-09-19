<?php

namespace Modules\FrontOffice\Filament\Resources\Guests\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\FrontOffice\Filament\Resources\Guests\GuestResource;

class CreateGuest extends CreateRecord
{
    protected static string $resource = GuestResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl('index');
    }
}
