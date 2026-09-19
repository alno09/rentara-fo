<?php

namespace Modules\FrontOffice\Filament\Resources\Reservations\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\FrontOffice\Filament\Resources\Reservations\ReservationResource;

class ListReservations extends ListRecords
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Reservation'),
        ];
    }
}