<?php

namespace Modules\FrontOffice\Actions\Reservations;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\FrontOffice\Enums\ReservationStatus;
use Modules\FrontOffice\Exceptions\RoomUnavailableException;
use Modules\FrontOffice\Models\Reservation;
use Modules\FrontOffice\Models\Room;
use Modules\FrontOffice\Services\RoomAvailabilityService;

final class AssignRoom
{
    public function __construct(
        private readonly RoomAvailabilityService $availabilityService,
    ) {
    }

    public function execute(
        Reservation $reservation,
        Room $room,
    ): Reservation {
        return DB::transaction(function () use (
            $reservation,
            $room
        ): Reservation {
            $reservation = Reservation::query()
                ->lockForUpdate()
                ->findOrFail($reservation->id);

            $room = Room::query()
                ->lockForUpdate()
                ->findOrFail($room->id);

            if (! in_array($reservation->status, [
                ReservationStatus::PENDING,
                ReservationStatus::CONFIRMED,
            ], true)) {
                throw new InvalidArgumentException(
                    'Room can only be assigned to an active reservation.'
                );
            }

            if ($room->room_type_id !== $reservation->room_type_id) {
                throw new InvalidArgumentException(
                    'Room type does not match the reservation.'
                );
            }

            if (! $this->availabilityService->isAvailable(
                room: $room,
                arrivalDate: $reservation->arrival_date,
                departureDate: $reservation->departure_date,
                ignoreReservationId: $reservation->id,
            )) {
                throw RoomUnavailableException::forRoom(
                    $room->room_number
                );
            }

            $reservation->update([
                'room_id' => $room->id,
            ]);

            return $reservation->refresh();
        }, attempts: 3);
    }
}