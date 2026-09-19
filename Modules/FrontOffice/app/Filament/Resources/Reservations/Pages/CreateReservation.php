<?php

namespace Modules\FrontOffice\Filament\Resources\Reservations\Pages;

use Carbon\CarbonImmutable;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\FrontOffice\Actions\Reservations\CreateReservation as CreateReservationAction;
use Modules\FrontOffice\Data\CreateReservationData;
use Modules\FrontOffice\Filament\Resources\Reservations\ReservationResource;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $payload = new CreateReservationData(
            guestId: (int) $data['guest_id'],

            roomTypeId: (int) $data['room_type_id'],

            arrivalDate: CarbonImmutable::parse(
                $data['arrival_date']
            ),

            departureDate: CarbonImmutable::parse(
                $data['departure_date']
            ),

            adultCount: (int) $data['adult_count'],

            childCount: (int) $data['child_count'],

            nightlyRate: (string) $data['nightly_rate'],

            roomId: isset($data['room_id'])
                ? (int) $data['room_id']
                : null,

            source: $data['source'] ?? null,

            notes: $data['notes'] ?? null,
        );

        return app(CreateReservationAction::class)
            ->execute($payload);
    }
}